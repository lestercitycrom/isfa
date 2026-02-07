<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\TenderItem;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class TenderItemsExport implements FromQuery, WithHeadings, WithMapping
{
	public function __construct(private readonly int $tenderId)
	{
	}

	public function query(): Builder
	{
		return TenderItem::query()
			->with('suppliers')
			->where('tender_id', $this->tenderId)
			->orderByRaw('external_id is null, external_id')
			->orderBy('id');
	}

	/**
	 * @return array<int, string>
	 */
	public function headings(): array
	{
		return [
			'ID',
			'Nomre',
			'Sekil',
			'Sekil URL',
			'Pozisiya adi',
			'Tesvir',
			'Miq. / vahid',
			'Techizatcilar',
		];
	}

	/**
	 * @param TenderItem $row
	 * @return array<int, mixed>
	 */
	public function map($row): array
	{
		$photoUrl = $this->photoUrl($row->photo_path);
		$quantityUnit = $row->quantity !== null
			? trim($this->formatQuantity((float) $row->quantity) . ' ' . (string) ($row->unit_of_measure ?? ''))
			: null;
		$suppliers = $row->suppliers->pluck('name')->filter()->implode(', ');

		return [
			$row->id,
			$row->external_id,
			$this->photoFormula($photoUrl),
			$photoUrl,
			$row->name,
			$row->description,
			$quantityUnit,
			$suppliers !== '' ? $suppliers : null,
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

	private function formatQuantity(float $quantity): string
	{
		return rtrim(rtrim(number_format($quantity, 4, '.', ' '), '0'), '.');
	}
}

