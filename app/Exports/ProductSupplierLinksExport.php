<?php

declare(strict_types=1);

namespace App\Exports;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class ProductSupplierLinksExport implements FromQuery, WithHeadings, WithMapping
{
	public function __construct(private readonly ?int $companyId)
	{
	}

	public function query(): Builder
	{
		return DB::table('product_supplier')
			->when($this->companyId !== null, fn ($q) => $q->where('company_id', $this->companyId))
			->orderBy('product_id')
			->orderBy('supplier_id');
	}

	/**
	 * @return array<int, string>
	 */
	public function headings(): array
	{
		return ['Mehsul ID', 'Techizatci ID', 'Status', 'Sertler'];
	}

	/**
	 * @param object $row
	 * @return array<int, mixed>
	 */
	public function map($row): array
	{
		return [
			$row->product_id,
			$row->supplier_id,
			$row->status,
			$row->terms,
		];
	}
}
