<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\TenderItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class TenderItemsExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithDrawings,
    WithEvents
{
    /** @var array<int, string> Excel row number => local image path to embed */
    private array $rowImagePaths = [];

    private int $excelRow = 1; // header is row 1

    public function __construct(private readonly int $tenderId)
    {
    }

    public function query(): Builder
    {
        return TenderItem::query()
            ->with('suppliers')
            ->where('tender_id', $this->tenderId)
            ->orderByRaw('CASE WHEN external_id IS NULL THEN 1 ELSE 0 END')
            ->orderBy('external_id')
            ->orderBy('id');
    }

    /**
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'ID',            // A
            'Nomre',         // B
            'Sekil',         // C (embedded image)
            'Sekil URL',     // D (clickable link)
            'Pozisiya adi',  // E
            'Tesvir',        // F (hidden)
            'Miq. / vahid',  // G
            'Techizatcilar', // H
        ];
    }

    /**
     * @param TenderItem $row
     * @return array<int, mixed>
     */
    public function map($row): array
    {
        $this->excelRow++; // move to current data row number in Excel

        $localPath = $this->localPhotoPathForExcel($row->photo_path);
        if ($localPath !== null) {
            $this->rowImagePaths[$this->excelRow] = $localPath;
        }

        $photoUrl = $this->photoUrl($row->photo_path);

        $quantityUnit = $row->quantity !== null
            ? trim($this->formatQuantity((float) $row->quantity) . ' ' . (string) ($row->unit_of_measure ?? ''))
            : null;

        $suppliers = $row->suppliers
            ->pluck('name')
            ->filter()
            ->unique()
            ->sort()
            ->implode(', ');

        return [
            (string) $row->id,
            $row->external_id !== null ? (string) $row->external_id : null,

            null, // C: image will be inserted via drawings()

            $photoUrl ? $this->photoHyperlinkFormula($photoUrl) : null,

            $row->name,
            $row->description,
            $quantityUnit,
            $suppliers !== '' ? $suppliers : null,
        ];
    }

    /**
     * Insert images into column C as embedded drawings.
     *
     * @return array<int, Drawing>
     */
    public function drawings(): array
    {
        $drawings = [];

        foreach ($this->rowImagePaths as $rowNumber => $path) {
            if (!is_file($path)) {
                continue;
            }

            $drawing = new Drawing();
            $drawing->setName('Photo');
            $drawing->setDescription('Photo');
            $drawing->setPath($path);
            $drawing->setCoordinates('C' . $rowNumber);
            $drawing->setOffsetX(5);
            $drawing->setOffsetY(5);

            // thumbnail size
            $drawing->setHeight(60);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                /** @var Worksheet $sheet */
                $sheet = $event->sheet->getDelegate();

                // Hide Tesvir column (F)
                $sheet->getColumnDimension('F')->setVisible(false);

                // Freeze header
                $sheet->freezePane('A2');

                // Filters
                $sheet->setAutoFilter('A1:H1');

                // Prevent Excel auto-format for IDs
                $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $sheet->getStyle('B:B')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                // Make image column wide enough + set row heights so images are visible
                $sheet->getColumnDimension('C')->setWidth(14);

                foreach (array_keys($this->rowImagePaths) as $rowNumber) {
                    $sheet->getRowDimension((int) $rowNumber)->setRowHeight(55);
                }
            },
        ];
    }

    private function photoUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $base = rtrim((string) config('app.url'), '/');

        return $base . '/storage/' . ltrim($path, '/');
    }

    private function photoHyperlinkFormula(string $photoUrl): string
    {
        $escaped = str_replace('"', '""', $photoUrl);

        return sprintf('=HYPERLINK("%s","View")', $escaped);
    }

    /**
     * Returns local filesystem path that PhpSpreadsheet can embed.
     * - Uses public disk: storage/app/public/{path}
     * - If WEBP: converts to a temp PNG using Imagick (your server supports it).
     */
    private function localPhotoPathForExcel(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $disk = Storage::disk('public');

        if (!$disk->exists($path)) {
            return null;
        }

        $fullPath = $disk->path($path);

        $ext = strtolower((string) pathinfo($fullPath, PATHINFO_EXTENSION));
        if ($ext !== 'webp') {
            return $fullPath;
        }

        return $this->convertWebpToPngTemp($fullPath);
    }

    private function convertWebpToPngTemp(string $webpPath): ?string
    {
        $tmpPng = sys_get_temp_dir() . '/excel_img_' . md5($webpPath) . '.png';

        if (is_file($tmpPng)) {
            return $tmpPng;
        }

        try {
            $img = new \Imagick($webpPath);
            $img->setImageFormat('png');
            $img->setImageCompressionQuality(85);
            $img->writeImage($tmpPng);
            $img->clear();
            $img->destroy();
        } catch (\Throwable $e) {
            return null;
        }

        return is_file($tmpPng) ? $tmpPng : null;
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 4, '.', ' '), '0'), '.');
    }
}