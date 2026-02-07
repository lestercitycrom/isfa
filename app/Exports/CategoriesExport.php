<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class CategoriesExport implements FromQuery, WithHeadings, WithMapping
{
	public function __construct(private readonly ?int $companyId)
	{
	}

	public function query(): Builder
	{
		return ProductCategory::query()
			->when($this->companyId !== null, fn ($q) => $q->where('company_id', $this->companyId))
			->orderBy('id');
	}

	/**
	 * @return array<int, string>
	 */
	public function headings(): array
	{
		return ['ID', 'Kateqoriya adi', 'Tesvir'];
	}

	/**
	 * @param ProductCategory $row
	 * @return array<int, mixed>
	 */
	public function map($row): array
	{
		return [
			$row->id,
			$row->name,
			$row->description,
		];
	}
}
