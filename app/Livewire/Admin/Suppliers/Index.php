<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Models\User;
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

		Supplier::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->whereKey($id)
			->delete();
		session()->flash('status', __('common.supplier_deleted'));
		$this->resetPage();
	}

	public function deleteAllSuppliers(): void
	{
		$companyId = CompanyContext::companyId();

		Supplier::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->delete();
		session()->flash('status', __('common.all_suppliers_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$suppliers = Supplier::query()
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

		return view('livewire.admin.suppliers.index', [
			'suppliers' => $suppliers,
			'companies' => $isAdmin ? User::query()->companies()->orderBy('company_name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
