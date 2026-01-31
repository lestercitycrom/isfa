<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Tender;
use App\Services\Etender\EtenderEventSyncService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Throwable;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';
	public ?string $eventTypeFilter = null;
	public ?string $eventStatusFilter = null;

	/**
	 * URL or numeric eventId entered by admin.
	 */
	public string $importUrl = '';

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function updatedEventTypeFilter(): void
	{
		$this->resetPage();
	}

	public function updatedEventStatusFilter(): void
	{
		$this->resetPage();
	}

	public function syncFromUrl(EtenderEventSyncService $syncService): void
	{
		$this->resetErrorBag();

		$import = trim($this->importUrl);

		if ($import === '') {
			$this->addError('importUrl', __('common.required'));

			return;
		}

		$eventId = $this->extractEventId($import);

		if ($eventId === null) {
			$this->addError(
				'importUrl',
				'Не удалось извлечь eventId. Ожидаю ссылку вида https://etender.gov.az/main/competition/detail/346012 или просто число 346012.'
			);

			return;
		}

		try {
			$tender = $syncService->sync($eventId);

			session()->flash('status', 'Тендер синхронизирован: #' . $tender->event_id);

			$this->redirectRoute('admin.tenders.show', ['tender' => $tender->getKey()]);
		} catch (Throwable $e) {
			report($e);

			$this->addError(
				'importUrl',
				'Ошибка синхронизации: ' . $e->getMessage()
			);
		}
	}

	private function extractEventId(string $value): ?int
	{
		$value = trim($value);

		// 1) If admin pasted numeric eventId only.
		if (ctype_digit($value)) {
			$asInt = (int) $value;

			return $asInt > 0 ? $asInt : null;
		}

		// 2) If admin pasted full URL.
		if (!filter_var($value, FILTER_VALIDATE_URL)) {
			return null;
		}

		// Typical: https://etender.gov.az/main/competition/detail/346012
		if (preg_match('~/(?:detail|competition/detail)/(?P<id>\d+)~i', $value, $m) === 1) {
			$asInt = (int) ($m['id'] ?? 0);

			return $asInt > 0 ? $asInt : null;
		}

		// Fallback: last numeric segment
		$last = (string) Str::of(parse_url($value, PHP_URL_PATH) ?: '')
			->trim('/')
			->explode('/')
			->last();

		if ($last !== '' && ctype_digit($last)) {
			$asInt = (int) $last;

			return $asInt > 0 ? $asInt : null;
		}

		return null;
	}

	public function render(): View
	{
		$tenders = Tender::query()
			->when($this->search !== '', function ($q): void {
				$q->where(function ($q): void {
					$q->where('title', 'like', '%' . $this->search . '%')
						->orWhere('organization_name', 'like', '%' . $this->search . '%')
						->orWhere('event_id', 'like', '%' . $this->search . '%')
						->orWhere('document_number', 'like', '%' . $this->search . '%');
				});
			})
			->when($this->eventTypeFilter !== null && $this->eventTypeFilter !== '', function ($q): void {
				$q->where('event_type_code', $this->eventTypeFilter);
			})
			->when($this->eventStatusFilter !== null && $this->eventStatusFilter !== '', function ($q): void {
				$q->where('event_status_code', $this->eventStatusFilter);
			})
			->orderByDesc('published_at')
			->paginate(20);

		$eventTypes = DictionaryValue::query()
			->where('dictionary', 'event_type')
			->orderBy('code')
			->get();

		$eventStatuses = DictionaryValue::query()
			->where('dictionary', 'event_status')
			->orderBy('code')
			->get();

		return view('livewire.admin.tenders.index', [
			'tenders' => $tenders,
			'eventTypes' => $eventTypes,
			'eventStatuses' => $eventStatuses,
		]);
	}
}
