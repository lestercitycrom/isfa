<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Tender;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Tender $tender;

	/**
	 * @var array<string, array<string, string>>
	 */
	public array $dictLabels = [];

	public function mount(Tender $tender): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $tender->company_id !== $companyId) {
			abort(403);
		}

		$this->tender = $tender->load([
			'items',
			'contacts',
			'announcements',
			'publishHistories',
		]);

		$this->dictLabels = $this->buildDictionaryLabelMaps([
			'event_type',
			'event_status',
			'document_view_type',
		]);
	}

	public function render(): View
	{
		return view('livewire.admin.tenders.show', [
			'originalUrl' => 'https://etender.gov.az/main/competition/detail/' . $this->tender->event_id,
		]);
	}

	/**
	 * @param array<int, string> $dictionaries
	 * @return array<string, array<string, string>>
	 */
	private function buildDictionaryLabelMaps(array $dictionaries): array
	{
		$rows = DictionaryValue::query()
			->whereIn('dictionary', $dictionaries)
			->orderBy('dictionary')
			->orderBy('code')
			->get();

		$maps = [];

		foreach ($rows as $row) {
			$dict = (string) $row->dictionary;
			$code = (string) $row->code;
			$label = $row->label !== null && trim((string) $row->label) !== ''
				? (string) $row->label
				: $code;

			$maps[$dict][$code] = $label;
		}

		return $maps;
	}
}
