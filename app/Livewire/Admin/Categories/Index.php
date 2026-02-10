<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\ProductCategory;
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
	use InteractsWithNotifications;

	public string $search = '';
	public ?int $companyFilter = null;

	public function updatedSearch(): void
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

		ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->whereKey($id)
			->delete();

		$this->notifySuccess(__('common.category_deleted'));
	}

	public function deleteAllCategories(): void
	{
		$companyId = CompanyContext::companyId();

		ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->delete();
		$this->notifySuccess(__('common.all_categories_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$categories = ProductCategory::query()
			->with('company')
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->when($isAdmin && $this->companyFilter !== null, function ($q): void {
				if ($this->companyFilter === 0) {
					$q->whereNull('company_id');

					return;
				}

				$q->where('company_id', $this->companyFilter);
			})
			->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.categories.index', [
			'categories' => $categories,
			'companies' => $isAdmin ? Company::query()->orderBy('name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
