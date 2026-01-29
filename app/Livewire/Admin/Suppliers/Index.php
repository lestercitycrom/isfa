<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

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

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function delete(int $id): void
	{
		Supplier::query()->whereKey($id)->delete();
		session()->flash('status', __('common.supplier_deleted'));
		$this->resetPage();
	}

	public function deleteAllSuppliers(): void
	{
		Supplier::query()->delete();
		session()->flash('status', __('common.all_suppliers_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$suppliers = Supplier::query()
			->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.suppliers.index', [
			'suppliers' => $suppliers,
		]);
	}
}
