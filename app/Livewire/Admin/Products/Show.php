<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
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
	public Product $product;
	public string $tab = 'details';
	public ?string $comment = null;

	public function mount(Product $product): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $product->company_id !== $companyId) {
			abort(403);
		}

		$this->product = $product->load(['category', 'suppliers']);
		$this->comment = $this->product->comment;
	}

	public function render(): View
	{
		return view('livewire.admin.products.show', [
			'activities' => $this->loadActivities(),
			'categoryMap' => ProductCategory::query()
				->when($this->product->company_id !== null, fn ($q) => $q->where('company_id', $this->product->company_id))
				->pluck('name', 'id')
				->toArray(),
		]);
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'history', 'comments'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
	}

	public function saveComment(): void
	{
		if (!Schema::hasColumn($this->product->getTable(), 'comment')) {
			session()->flash('status', __('common.comment_column_missing'));

			return;
		}

		$this->product->update([
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
			->where('subject_type', $this->product->getMorphClass())
			->where('subject_id', $this->product->getKey())
			->latest()
			->limit(50)
			->get();
	}
}
