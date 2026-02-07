<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
	public function __construct(private readonly ?int $companyId)
	{
	}

	public function query(): Builder
	{
		return Product::query()
			->with('category')
			->when($this->companyId !== null, fn ($q) => $q->where('company_id', $this->companyId))
			->orderBy('id');
	}

	/**
	 * @return array<int, string>
	 */
	public function headings(): array
	{
		return ['ID', 'Kateqoriya', 'Mehsul adi', 'Tesvir', 'Reng', 'Olcu vahidi', 'Xususiyyetler', 'Sekil', 'Sekil URL'];
	}

	/**
	 * @param Product $row
	 * @return array<int, mixed>
	 */
	public function map($row): array
	{
		$photoUrl = $this->photoUrl($row->photo_path);

		return [
			$row->id,
			$row->category?->name,
			$row->name,
			$row->description,
			$row->color,
			$row->unit,
			$row->characteristics,
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
