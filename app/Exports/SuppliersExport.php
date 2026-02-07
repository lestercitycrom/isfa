<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class SuppliersExport implements FromQuery, WithHeadings, WithMapping
{
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
			$row->comment,
			$this->photoFormula($photoUrl),
			$photoUrl,
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

	private function photoFormula(?string $photoUrl): ?string
	{
		if ($photoUrl === null || $photoUrl === '') {
			return null;
		}

		$escapedUrl = str_replace('"', '""', $photoUrl);

		return sprintf('=IMAGE("%s")', $escapedUrl);
	}
}
