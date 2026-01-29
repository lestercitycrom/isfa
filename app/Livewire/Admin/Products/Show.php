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

		session()->flash('status', 'Supplier linked.');

		$this->redirectRoute('admin.products.show', ['product' => $this->product->id]);
	}

	public function savePivot(int $supplierId): void
	{
		$status = ProductSupplierStatus::tryFrom($this->pivotStatus[$supplierId] ?? 'reserve') ?? ProductSupplierStatus::Reserve;

		$this->product->suppliers()->updateExistingPivot($supplierId, [
			'status' => $status->value,
			'terms' => $this->pivotTerms[$supplierId] ?? null,
		]);

		session()->flash('status', 'Link updated.');

		$this->redirectRoute('admin.products.show', ['product' => $this->product->id]);
	}

	public function detach(int $supplierId): void
	{
		$this->product->suppliers()->detach($supplierId);

		session()->flash('status', 'Supplier unlinked.');

		$this->redirectRoute('admin.products.show', ['product' => $this->product->id]);
	}

	public function render(): View
	{
		return view('livewire.admin.products.show', [
			'suppliers' => Supplier::query()->orderBy('name')->get(),
		]);
	}
}
