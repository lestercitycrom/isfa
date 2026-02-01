<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Tender;
use App\Models\User;
use App\Services\Etender\EtenderEventSyncService;
use App\Support\CompanyContext;
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
	public ?int $companyFilter = null;

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

	public function updatedCompanyFilter(): void
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
			$this->addError('importUrl', __('tenders.errors.cant_extract_event_id'));

			return;
		}

		try {
			$tender = $syncService->sync($eventId, CompanyContext::companyId());

			session()->flash('status', __('tenders.flash.synced', ['id' => $tender->event_id]));

			$this->redirectRoute('admin.tenders.show', ['tender' => $tender->getKey()]);
		} catch (Throwable $e) {
			report($e);

			$this->addError('importUrl', __('tenders.errors.sync_error', ['message' => $e->getMessage()]));
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

		// Fallback: last numeric segment.
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
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$tenders = Tender::query()
			->with('company')
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->when($isAdmin && $this->companyFilter !== null, fn ($q) => $q->where('company_id', $this->companyFilter))
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
			'companies' => $isAdmin ? User::query()->companies()->orderBy('company_name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
