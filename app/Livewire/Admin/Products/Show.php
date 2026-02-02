<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Enums\ProductSupplierStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
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

	public int $attachSupplierId = 0;
	public string $attachStatus = 'reserve';
	public ?string $attachTerms = null;

	/**
	 * @var array<int, string>
	 */
	public array $pivotStatus = [];

	/**
	 * @var array<int, string|null>
	 */
	public array $pivotTerms = [];

	public function mount(Product $product): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $product->company_id !== $companyId) {
			abort(403);
		}

		$this->product = $product->load(['category', 'suppliers']);
		$this->comment = $this->product->comment;

		foreach ($this->product->suppliers as $supplier) {
			$this->pivotStatus[(int) $supplier->id] = $supplier->pivot->status instanceof ProductSupplierStatus 
				? $supplier->pivot->status->value 
				: (string) $supplier->pivot->status;
			$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
		}
	}

	public function attach(): void
	{
		$companyId = $this->product->company_id;
		$existsRule = Rule::exists('suppliers', 'id');
		if ($companyId !== null) {
			$existsRule->where('company_id', $companyId);
		}

		$this->validate([
			'attachSupplierId' => [
				'required',
				'integer',
				$existsRule,
			],
			'attachStatus' => ['required', 'in:primary,reserve'],
			'attachTerms' => ['nullable', 'string'],
		]);

		$this->product->suppliers()->syncWithoutDetaching([
			$this->attachSupplierId => [
				'status' => $this->attachStatus,
				'terms' => $this->attachTerms,
			],
		]);

		$this->product->refresh()->load('suppliers');

		// Update pivot arrays
		foreach ($this->product->suppliers as $supplier) {
			$this->pivotStatus[(int) $supplier->id] = $supplier->pivot->status->value;
			$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
		}

		// Reset form
		$this->attachSupplierId = 0;
		$this->attachStatus = 'reserve';
		$this->attachTerms = null;

		session()->flash('status', __('common.supplier_linked'));
	}

	public function savePivot(int $supplierId): void
	{
		$status = ProductSupplierStatus::tryFrom($this->pivotStatus[$supplierId] ?? 'reserve') ?? ProductSupplierStatus::Reserve;

		$this->product->suppliers()->updateExistingPivot($supplierId, [
			'status' => $status->value,
			'terms' => $this->pivotTerms[$supplierId] ?? null,
		]);

		session()->flash('status', __('common.link_updated'));
	}

	public function detach(int $supplierId): void
	{
		$this->product->suppliers()->detach($supplierId);

		// Remove from arrays
		unset($this->pivotStatus[$supplierId], $this->pivotTerms[$supplierId]);

		$this->product->refresh()->load('suppliers');

		session()->flash('status', __('common.supplier_unlinked'));
	}

	public function render(): View
	{
		return view('livewire.admin.products.show', [
			'suppliers' => Supplier::query()
				->when($this->product->company_id !== null, fn ($q) => $q->where('company_id', $this->product->company_id))
				->orderBy('name')
				->get(),
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

		session()->flash('status', __('common.saved'));
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
