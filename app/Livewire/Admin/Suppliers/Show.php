<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Supplier $supplier;
	public string $tab = 'details';
	public ?string $comment = null;

	public function mount(Supplier $supplier): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $supplier->company_id !== $companyId) {
			abort(403);
		}

		$this->supplier = $supplier->load([
			'products' => function ($q) use ($companyId): void {
				if ($companyId !== null) {
					$q->where('products.company_id', $companyId);
				}

				$q->with('category');
			},
		]);

		$this->comment = $this->supplier->comment;
	}

	public function render(): View
	{
		return view('livewire.admin.suppliers.show', [
			'activities' => $this->loadActivities(),
		]);
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'history', 'comments'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
	}

	public function saveComment(): void
	{
		if (!Schema::hasColumn($this->supplier->getTable(), 'comment')) {
			session()->flash('status', __('common.comment_column_missing'));

			return;
		}

		$this->supplier->update([
			'comment' => $this->comment,
		]);

		$this->dispatch('comment-saved');
	}

	/**
	 * @return Collection<int, Activity>
	 */
	private function loadActivities(): Collection
	{
		return Activity::query()
			->where('subject_type', $this->supplier->getMorphClass())
			->where('subject_id', $this->supplier->getKey())
			->latest()
			->limit(50)
			->get();
	}
}
