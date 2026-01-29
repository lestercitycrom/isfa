<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Enums\ProductSupplierStatus;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Product $product;

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
		$this->product = $product->load(['category', 'suppliers']);

		foreach ($this->product->suppliers as $supplier) {
			$this->pivotStatus[(int) $supplier->id] = (string) $supplier->pivot->status;
			$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
		}
	}

	public function attach(): void
	{
		$this->validate([
			'attachSupplierId' => ['required', 'integer', 'exists:suppliers,id'],
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
			'suppliers' => Supplier::query()->orderBy('name')->get(),
		]);
	}
}
