<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';

	public ?int $editingId = null;
	public bool $showModal = false;
	public string $name = '';
	public ?string $description = null;

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function startCreate(): void
	{
		$this->editingId = null;
		$this->name = '';
		$this->description = null;
		$this->showModal = true;
	}

	public function startEdit(int $id): void
	{
		$category = ProductCategory::query()->findOrFail($id);

		$this->editingId = $category->id;
		$this->name = $category->name;
		$this->description = $category->description;
		$this->showModal = true;
	}

	public function save(): void
	{
		$this->validate([
			'name' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
		]);

		ProductCategory::query()->updateOrCreate(
			['id' => $this->editingId],
			[
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
		ProductCategory::query()->whereKey($id)->delete();

		session()->flash('status', __('common.category_deleted'));
	}

	public function deleteAllCategories(): void
	{
		ProductCategory::query()->delete();
		session()->flash('status', __('common.all_categories_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$categories = ProductCategory::query()
			->when($this->search !== '', fn ($q) => $q->where('name', 'like', '%' . $this->search . '%'))
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.categories.index', [
			'categories' => $categories,
		]);
	}
}
