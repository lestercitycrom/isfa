<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Models\ProductCategory;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';
	public ?int $companyFilter = null;

	public ?int $editingId = null;
	public bool $showModal = false;
	public string $name = '';
	public ?string $description = null;
	public ?int $company_id = null;

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function updatedCompanyFilter(): void
	{
		$this->resetPage();
	}

	public function startCreate(): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$this->editingId = null;
		$this->name = '';
		$this->description = null;
		$this->company_id = $isAdmin ? null : $companyId;
		$this->showModal = true;
	}

	public function startEdit(int $id): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$category = ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->findOrFail($id);

		$this->editingId = $category->id;
		$this->name = $category->name;
		$this->description = $category->description;
		$this->company_id = $category->company_id;
		$this->showModal = true;
	}

	public function save(): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}

		$this->validate([
			'name' => [
				'required',
				'string',
				'max:255',
				Rule::unique('product_categories', 'name')
					->where(fn ($q) => $this->company_id !== null
						? $q->where('company_id', $this->company_id)
						: $q->whereNull('company_id')
					)
					->ignore($this->editingId),
			],
			'description' => ['nullable', 'string'],
			'company_id' => ['nullable', 'integer', 'exists:users,id'],
		], [
			'name.unique' => __('common.category_name_already_exists'),
		]);

		ProductCategory::query()->updateOrCreate(
			['id' => $this->editingId],
			[
				'company_id' => $this->company_id,
				'name' => $this->name,
				'description' => $this->description,
			]
		);

		$this->showModal = false;
		$this->startCreate();

		session()->flash('status', __('common.category_saved'));
	}

	public function delete(int $id): void
	{
		$companyId = CompanyContext::companyId();

		ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->whereKey($id)
			->delete();

		session()->flash('status', __('common.category_deleted'));
	}

	public function deleteAllCategories(): void
	{
		$companyId = CompanyContext::companyId();

		ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->delete();
		session()->flash('status', __('common.all_categories_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$categories = ProductCategory::query()
			->with('company')
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->when($isAdmin && $this->companyFilter !== null, fn ($q) => $q->where('company_id', $this->companyFilter))
			->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.categories.index', [
			'categories' => $categories,
			'companies' => $isAdmin ? User::query()->companies()->orderBy('company_name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
