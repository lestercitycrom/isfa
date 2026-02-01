<?php

declare(strict_types=1);

namespace App\Livewire\Admin\ActivityLogs;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tender;
use App\Models\TenderAnnouncement;
use App\Models\TenderContact;
use App\Models\TenderItem;
use App\Models\TenderPublishHistory;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';

	public string $event = '';

	public string $subjectType = '';

	public int $perPage = 20;

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function updatedEvent(): void
	{
		$this->resetPage();
	}

	public function updatedSubjectType(): void
	{
		$this->resetPage();
	}

	public function render(): View
	{
		$activities = $this->activityQuery()->paginate($this->perPage);

		return view('livewire.admin.activity-logs.index', [
			'activities' => $activities,
			'eventOptions' => $this->eventOptions(),
			'subjectOptions' => $this->subjectOptions(),
		]);
	}

	/**
	 * @return array<string, string>
	 */
	private function eventOptions(): array
	{
		return [
			'created' => __('common.event_created'),
			'updated' => __('common.event_updated'),
			'deleted' => __('common.event_deleted'),
			'attached' => __('common.event_attached'),
			'detached' => __('common.event_detached'),
		];
	}

	/**
	 * @return array<string, string>
	 */
	private function subjectOptions(): array
	{
		return [
			Tender::class => __('common.tender'),
			TenderItem::class => __('common.tender_item'),
			TenderContact::class => __('common.tender_contact'),
			TenderAnnouncement::class => __('common.tender_announcement'),
			TenderPublishHistory::class => __('common.tender_publish_history'),
			Product::class => __('common.product'),
			ProductCategory::class => __('common.category'),
			Supplier::class => __('common.supplier'),
			User::class => __('common.company'),
		];
	}

	private function activityQuery(): Builder
	{
		$query = Activity::query()
			->with(['causer', 'subject'])
			->latest();

		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null) {
			$query->where('company_id', $companyId);
		}

		if ($this->event !== '') {
			$query->where('event', $this->event);
		}

		if ($this->subjectType !== '') {
			$query->where('subject_type', $this->subjectType);
		}

		$search = trim($this->search);

		if ($search !== '') {
			$query->where(function (Builder $builder) use ($search): void {
				$builder
					->where('description', 'like', '%' . $search . '%')
					->orWhere('properties->attributes->name', 'like', '%' . $search . '%')
					->orWhere('properties->attributes->title', 'like', '%' . $search . '%')
					->orWhereHasMorph(
						'causer',
						[User::class],
						fn (Builder $sub) => $sub->where('name', 'like', '%' . $search . '%')
					);
			});
		}

		return $query;
	}
}
