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
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?ProductCategory $category = null;

	public string $tab = 'details';

	public ?int $company_id = null;
	public string $name = '';
	public ?string $description = null;

	public function mount(?ProductCategory $category = null): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$this->category = $category;

		if ($category !== null) {
			if (!$isAdmin && $companyId !== null && (int) $category->company_id !== $companyId) {
				abort(403);
			}

			$this->company_id = $category->company_id;
			$this->name = $category->name;
			$this->description = $category->description;
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'history'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
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
					->ignore($this->category?->id),
			],
			'description' => ['nullable', 'string'],
			'company_id' => ['nullable', 'integer', 'exists:users,id'],
		], [
			'name.unique' => __('common.category_name_already_exists'),
		]);

		$category = ProductCategory::query()->updateOrCreate(
			['id' => $this->category?->id],
			[
				'company_id' => $this->company_id,
				'name' => $this->name,
				'description' => $this->description,
			]
		);

		session()->flash('status', __('common.category_saved'));

		$this->redirectRoute('admin.categories.index');
	}

	public function render(): View
	{
		return view('livewire.admin.categories.edit', [
			'companies' => CompanyContext::isAdmin()
				? User::query()->companies()->orderBy('company_name')->get()
				: collect(),
			'isAdmin' => CompanyContext::isAdmin(),
			'activities' => $this->loadActivities(),
		]);
	}

	/**
	 * @return Collection<int, Activity>
	 */
	private function loadActivities(): Collection
	{
		if ($this->category === null) {
			return collect();
		}

		return Activity::query()
			->where('subject_type', $this->category->getMorphClass())
			->where('subject_id', $this->category->getKey())
			->latest()
			->limit(50)
			->get();
	}
}
