<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';
	public string $categoryFilter = '';
	public string $supplierFilter = '';
	public ?int $companyFilter = null;

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

	public function updatedCompanyFilter(): void
	{
		$this->resetPage();
	}

	public function delete(int $id): void
	{
		$companyId = CompanyContext::companyId();

		Product::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->whereKey($id)
			->delete();
		session()->flash('status', __('common.product_deleted'));
		$this->resetPage();
	}

	public function deleteAllProducts(): void
	{
		$companyId = CompanyContext::companyId();

		Product::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->delete();
		session()->flash('status', __('common.all_products_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$products = Product::query()
			->with([
				'category',
				'company',
				'suppliers' => fn ($q) => $q->orderByDesc('product_supplier.created_at'),
			])
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->when($isAdmin && $this->companyFilter !== null, function ($q): void {
				if ($this->companyFilter === 0) {
					$q->whereNull('company_id');

					return;
				}

				$q->where('company_id', $this->companyFilter);
			})
			->when($this->search !== '', function ($q): void {
				$q->where('name', 'like', '%' . $this->search . '%');
			})
			->when($this->categoryFilter !== '', function ($q): void {
				$q->whereHas('category', function ($q): void {
					$q->where('name', $this->categoryFilter);
				});
			})
			->when($this->supplierFilter !== '', function ($q): void {
				$q->whereHas('suppliers', function ($q): void {
					$q->where('suppliers.name', $this->supplierFilter);
				});
			})
			->orderBy('name')
			->paginate(15);

		$categories = ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->select('name')
			->whereNotNull('name')
			->distinct()
			->orderBy('name')
			->pluck('name');
		$suppliers = Supplier::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->select('name')
			->whereNotNull('name')
			->distinct()
			->orderBy('name')
			->pluck('name');

		return view('livewire.admin.products.index', [
			'products' => $products,
			'categories' => $categories,
			'suppliers' => $suppliers,
			'companies' => $isAdmin ? Company::query()->orderBy('name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
