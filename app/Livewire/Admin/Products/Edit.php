<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Enums\ProductSupplierStatus;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Company;
use App\Models\Supplier;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	use WithFileUploads;

	public ?Product $product = null;

	public ?int $company_id = null;
	public ?int $category_id = null;
	public string $name = '';
	public ?string $description = null;
	public mixed $photo = null;

	public int $attachSupplierId = 0;
	public string $attachStatus = 'primary';
	public ?string $attachTerms = null;

	/**
	 * @var array<int, string>
	 */
	public array $pivotStatus = [];

	/**
	 * @var array<int, string|null>
	 */
	public array $pivotTerms = [];

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

			$this->product->load('suppliers');
			foreach ($this->product->suppliers as $supplier) {
				$this->pivotStatus[(int) $supplier->id] = $supplier->pivot->status instanceof ProductSupplierStatus
					? $supplier->pivot->status->value
					: (string) $supplier->pivot->status;
				$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
			}
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
			'photo' => ['nullable', 'image', 'max:4096'],
		], [
			'name.unique' => __('common.product_name_already_exists'),
		]);

		$photoPath = $this->product?->photo_path;
		if ($this->photo) {
			if ($photoPath) {
				Storage::disk('public')->delete($photoPath);
			}
			$photoPath = $this->photo->store('products', 'public');
		}

		$product = Product::query()->updateOrCreate(
			['id' => $this->product?->id],
			[
				'company_id' => $this->company_id,
				'category_id' => $this->category_id,
				'name' => $this->name,
				'description' => $this->description,
				'photo_path' => $photoPath,
			]
		);

		session()->flash('status', __('common.product_saved'));

		$this->redirectRoute('admin.products.index');
	}

	public function attach(): void
	{
		if (!$this->product?->exists) {
			return;
		}

		$companyId = $this->product->company_id;
		$existsRule = Rule::exists('suppliers', 'id');
		if ($companyId !== null) {
			$existsRule->where('company_id', $companyId);
		}

		$this->validate([
			'attachSupplierId' => [
				'required',
				'integer',
				$existsRule,
			],
			'attachStatus' => ['required', 'in:primary,reserve'],
			'attachTerms' => ['nullable', 'string'],
		]);

		$this->product->suppliers()->syncWithoutDetaching([
			$this->attachSupplierId => [
				'status' => $this->attachStatus,
				'terms' => $this->attachTerms,
			],
		]);

		$this->product->refresh()->load('suppliers');

		foreach ($this->product->suppliers as $supplier) {
			$this->pivotStatus[(int) $supplier->id] = $supplier->pivot->status->value;
			$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
		}

		$this->attachSupplierId = 0;
		$this->attachStatus = 'primary';
		$this->attachTerms = null;

		session()->flash('status', __('common.supplier_linked'));
	}

	public function savePivot(int $supplierId): void
	{
		if (!$this->product?->exists) {
			return;
		}

		$status = ProductSupplierStatus::tryFrom($this->pivotStatus[$supplierId] ?? 'primary') ?? ProductSupplierStatus::Primary;

		$this->product->suppliers()->updateExistingPivot($supplierId, [
			'status' => $status->value,
			'terms' => $this->pivotTerms[$supplierId] ?? null,
		]);

		session()->flash('status', __('common.link_updated'));
	}

	public function detach(int $supplierId): void
	{
		if (!$this->product?->exists) {
			return;
		}

		$this->product->suppliers()->detach($supplierId);
		unset($this->pivotStatus[$supplierId], $this->pivotTerms[$supplierId]);

		$this->product->refresh()->load('suppliers');

		session()->flash('status', __('common.supplier_unlinked'));
	}

	public function render(): View
	{
		return view('livewire.admin.products.edit', [
			'categories' => ProductCategory::query()
				->when($this->company_id !== null, fn ($q) => $q->where('company_id', $this->company_id))
				->orderBy('name')
				->get(),
			'suppliers' => $this->product?->exists
				? Supplier::query()
					->when($this->product->company_id !== null, fn ($q) => $q->where('company_id', $this->product->company_id))
					->orderBy('name')
					->get()
				: collect(),
			'companies' => CompanyContext::isAdmin()
				? Company::query()->orderBy('name')->get()
				: collect(),
			'isAdmin' => CompanyContext::isAdmin(),
		]);
	}
}
