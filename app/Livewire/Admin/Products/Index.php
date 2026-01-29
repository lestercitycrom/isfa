<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';
	public ?int $categoryFilter = null;
	public ?int $supplierFilter = null;

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function updatedCategoryFilter(): void
	{
		$this->resetPage();
	}

	public function updatedSupplierFilter(): void
	{
		$this->resetPage();
	}

	public function delete(int $id): void
	{
		Product::query()->whereKey($id)->delete();
		session()->flash('status', __('common.product_deleted'));
		$this->resetPage();
	}

	public function deleteAllProducts(): void
	{
		Product::query()->delete();
		session()->flash('status', __('common.all_products_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$products = Product::query()
			->with('category', 'suppliers')
			->when($this->search !== '', function ($q): void {
				$q->where('name', 'like', '%' . $this->search . '%');
			})
			->when($this->categoryFilter !== null, function ($q): void {
				$q->where('category_id', $this->categoryFilter);
			})
			->when($this->supplierFilter !== null, function ($q): void {
				$q->whereHas('suppliers', function ($q): void {
					$q->where('suppliers.id', $this->supplierFilter);
				});
			})
			->orderBy('name')
			->paginate(15);

		$categories = ProductCategory::query()->orderBy('name')->get();
		$suppliers = Supplier::query()->orderBy('name')->get();

		return view('livewire.admin.products.index', [
			'products' => $products,
			'categories' => $categories,
			'suppliers' => $suppliers,
		]);
	}
}
