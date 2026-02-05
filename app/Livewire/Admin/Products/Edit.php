<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?Product $product = null;

	public ?int $company_id = null;
	public ?int $category_id = null;
	public string $name = '';
	public ?string $description = null;

	public function mount(?Product $product = null): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$this->product = $product;

		if ($product !== null) {
			if (!$isAdmin && $companyId !== null && (int) $product->company_id !== $companyId) {
				abort(403);
			}

			$this->company_id = $product->company_id;
			$this->category_id = $product->category_id;
			$this->name = $product->name;
			$this->description = $product->description;
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function save(): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}

		$categoryRule = Rule::exists('product_categories', 'id');
		if ($this->company_id !== null) {
			$categoryRule->where('company_id', $this->company_id);
		}

		// Уникальность: компания + название + категория (в рамках компании — только свои товары)
		$nameRule = Rule::unique('products', 'name')
			->where(function ($q): void {
				if ($this->company_id !== null) {
					$q->where('company_id', $this->company_id);
				}
				if ($this->category_id !== null) {
					$q->where('category_id', $this->category_id);
				} else {
					$q->whereNull('category_id');
				}
			})
			->ignore($this->product?->id);

		$this->validate([
			'company_id' => ['nullable', 'integer', 'exists:companies,id'],
			'category_id' => [
				'nullable',
				'integer',
				$categoryRule,
			],
			'name' => [
				'required',
				'string',
				'max:255',
				$nameRule,
			],
			'description' => ['nullable', 'string'],
		], [
			'name.unique' => __('common.product_name_already_exists'),
		]);

		$product = Product::query()->updateOrCreate(
			['id' => $this->product?->id],
			[
				'company_id' => $this->company_id,
				'category_id' => $this->category_id,
				'name' => $this->name,
				'description' => $this->description,
			]
		);

		session()->flash('status', __('common.product_saved'));

		$this->redirectRoute('admin.products.index');
	}

	public function render(): View
	{
		return view('livewire.admin.products.edit', [
			'categories' => ProductCategory::query()
				->when($this->company_id !== null, fn ($q) => $q->where('company_id', $this->company_id))
				->orderBy('name')
				->get(),
			'companies' => CompanyContext::isAdmin()
				? Company::query()->orderBy('name')->get()
				: collect(),
			'isAdmin' => CompanyContext::isAdmin(),
		]);
	}
}
