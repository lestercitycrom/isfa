<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Company;
use App\Models\Supplier;
use App\Models\Tag;
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

    public ?int $tagFilter = null;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedCompanyFilter(): void
    {
        $this->tagFilter = null;
        $this->resetPage();
    }

    public function updatedTagFilter(): void
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
        $this->notifySuccess(__('common.supplier_deleted'));
        $this->resetPage();
    }

    public function deleteAllSuppliers(): void
    {
        $companyId = CompanyContext::companyId();

        Supplier::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->delete();
        $this->notifySuccess(__('common.all_suppliers_deleted'));
        $this->resetPage();
    }

    public function render(): View
    {
        $companyId = CompanyContext::companyId();
        $isAdmin = CompanyContext::isAdmin();

        $suppliers = Supplier::query()
            ->with(['company', 'tags'])
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->when($isAdmin && $this->companyFilter !== null, function ($q): void {
                if ($this->companyFilter === 0) {
                    $q->whereNull('company_id');

                    return;
                }

                $q->where('company_id', $this->companyFilter);
            })
            ->when($this->tagFilter !== null && $this->tagFilter > 0, function ($q): void {
                $q->whereHas('tags', fn ($tagsQ) => $tagsQ->whereKey($this->tagFilter));
            })
            ->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(15);

        $tagOptions = Tag::query()
            ->where(function ($q) use ($companyId, $isAdmin): void {
                if ($companyId !== null) {
                    $q->where('company_id', $companyId);

                    return;
                }

                if ($isAdmin && $this->companyFilter !== null) {
                    if ($this->companyFilter === 0) {
                        $q->whereNull('company_id');

                        return;
                    }

                    $q->where('company_id', $this->companyFilter);

                    return;
                }

                if (! $isAdmin) {
                    $q->whereNull('company_id');
                }
            })
            ->orderBy('name')
            ->get();

        return view('livewire.admin.suppliers.index', [
            'suppliers' => $suppliers,
            'companies' => $isAdmin ? Company::query()->orderBy('name')->get() : collect(),
            'tagOptions' => $tagOptions,
            'isAdmin' => $isAdmin,
        ]);
    }
}
