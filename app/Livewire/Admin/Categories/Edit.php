<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Categories;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\ProductCategory;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Collection;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	use InteractsWithNotifications;

	public ?ProductCategory $category = null;

	public string $tab = 'details';

	public ?int $company_id = null;
	public string $name = '';
	public ?string $description = null;
	public ?string $comment = null;

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
			$this->comment = $category->comment;
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'history', 'comments'];

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
			'company_id' => ['nullable', 'integer', 'exists:companies,id'],
		], [
			'name.unique' => __('common.category_name_already_exists'),
		]);

		$payload = [
			'company_id' => $this->company_id,
			'name' => $this->name,
			'description' => $this->description,
		];

		if (Schema::hasColumn((new ProductCategory())->getTable(), 'comment')) {
			$payload['comment'] = $this->comment;
		}

		$category = ProductCategory::query()->updateOrCreate(
			['id' => $this->category?->id],
			$payload
		);

		$this->flashSuccessToast(__('common.category_saved'));

		$this->redirectRoute('admin.categories.index');
	}

	public function render(): View
	{
		return view('livewire.admin.categories.edit', [
			'companies' => CompanyContext::isAdmin()
				? Company::query()->orderBy('name')->get()
				: collect(),
			'isAdmin' => CompanyContext::isAdmin(),
			'activities' => $this->loadActivities(),
		]);
	}

	public function saveComment(): void
	{
		if ($this->category === null) {
			return;
		}

		if (!Schema::hasColumn($this->category->getTable(), 'comment')) {
			$this->notifyError(__('common.comment_column_missing'));

			return;
		}

		$this->category->update([
			'comment' => $this->comment,
		]);

		$this->dispatch('comment-saved');
		$this->notifySuccess(__('common.saved'));
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
