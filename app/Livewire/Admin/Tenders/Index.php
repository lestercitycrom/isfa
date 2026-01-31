<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Tender;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';
	public ?string $eventTypeFilter = null;
	public ?string $eventStatusFilter = null;

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

	public function resetFilters(): void
	{
		$this->search = '';
		$this->eventTypeFilter = null;
		$this->eventStatusFilter = null;

		$this->resetPage();
	}

	public function render(): View
	{
		$tenders = Tender::query()
			->when($this->search !== '', function ($q): void {
				$term = '%' . $this->search . '%';

				$q->where(function ($q) use ($term): void {
					$q->where('title', 'like', $term)
						->orWhere('organization_name', 'like', $term)
						->orWhere('organization_voen', 'like', $term)
						->orWhere('document_number', 'like', $term)
						->orWhere('event_id', 'like', $term);
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
