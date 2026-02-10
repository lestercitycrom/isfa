<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\ResolvesExcelImagePath;
use App\Models\TenderItem;
use Illuminate\Database\Eloquent\Builder;
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
    use ResolvesExcelImagePath;

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
            'Sekil',         // B (embedded image)
            'Nomre',         // C
            'Pozisiya adi',  // D
            'Tesvir',        // E (hidden)
            'Miq. / vahid',  // F
            'Techizatcilar', // G
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
            null, // B: image will be inserted via drawings()
            $row->external_id !== null ? (string) $row->external_id : null,
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
            $drawing->setCoordinates('B' . $rowNumber);
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

                // Hide Tesvir column (E)
                $sheet->getColumnDimension('E')->setVisible(false);

                // Freeze header
                $sheet->freezePane('A2');

                // Filters
                $sheet->setAutoFilter('A1:G1');

                // Prevent Excel auto-format for IDs
                $sheet->getStyle('A:A')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
                $sheet->getStyle('C:C')->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);

                // Make image column wide enough + set row heights so images are visible
                $sheet->getColumnDimension('B')->setWidth(14);

                foreach (array_keys($this->rowImagePaths) as $rowNumber) {
                    $sheet->getRowDimension((int) $rowNumber)->setRowHeight(55);
                }
            },
        ];
    }

    private function formatQuantity(float $quantity): string
    {
        return rtrim(rtrim(number_format($quantity, 4, '.', ' '), '0'), '.');
    }
}
