<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Concerns\ResolvesExcelImagePath;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

final class SuppliersExport implements FromQuery, WithHeadings, WithMapping, WithDrawings, WithEvents
{
	use ResolvesExcelImagePath;

	/** @var array<int, string> */
	private array $rowImagePaths = [];

	private int $excelRow = 1;

	public function __construct(private readonly ?int $companyId)
	{
	}

	public function query(): Builder
	{
		return Supplier::query()
			->when($this->companyId !== null, fn ($q) => $q->where('company_id', $this->companyId))
			->orderBy('id');
	}

	/**
	 * @return array<int, string>
	 */
	public function headings(): array
	{
		return [
			'ID',
			'Techizatci adi',
			'VOEN',
			'Elaqedar sexs',
			'Telefon',
			'Email',
			'Veb sayt',
			'Odenis novu',
			'Kart nomresi',
			'Routing nomresi',
			'Rekvizitlər',
			'Qeyd',
			'Sekil',
			'Sekil URL',
		];
	}

	/**
	 * @param Supplier $row
	 * @return array<int, mixed>
	 */
	public function map($row): array
	{
		$this->excelRow++;

		$localPath = $this->localPhotoPathForExcel($row->photo_path);
		if ($localPath !== null) {
			$this->rowImagePaths[$this->excelRow] = $localPath;
		}

		$photoUrl = $this->photoUrl($row->photo_path);

		return [
			$row->id,
			$row->name,
			$row->voen,
			$row->contact_name,
			$row->phone,
			$row->email,
			$row->website,
			$row->payment_method,
			$row->payment_card_number,
			$row->payment_routing_number,
			$row->payment_requisites,
			$row->comment,
			null,
			$photoUrl,
		];
	}

	/**
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
			$drawing->setCoordinates('M' . $rowNumber);
			$drawing->setOffsetX(5);
			$drawing->setOffsetY(5);
			$drawing->setHeight(60);

			$drawings[] = $drawing;
		}

		return $drawings;
	}

	public function registerEvents(): array
	{
		return [
			AfterSheet::class => function (AfterSheet $event): void {
				/** @var Worksheet $sheet */
				$sheet = $event->sheet->getDelegate();
				$sheet->getColumnDimension('M')->setWidth(14);

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

}
