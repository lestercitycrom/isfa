<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Enums\ProductSupplierStatus;
use App\Models\Company;
use App\Models\Product;
use App\Models\ProductAttributeDefinition;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	use WithFileUploads;

	public ?Product $product = null;
	public string $tab = 'details';

	public ?int $company_id = null;
	public ?int $category_id = null;
	public string $name = '';
	public ?string $description = null;
	public ?string $color = null;
	public ?string $unit = null;
	public ?string $characteristics = null;
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

	public string $newAttributeLabel = '';
	public string $newAttributeCode = '';
	public string $newAttributeType = 'text';

	/**
	 * @var array<int, string>
	 */
	public array $attributeValues = [];

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
			$this->color = $product->color;
			$this->unit = $product->unit;
			$this->characteristics = $product->characteristics;

			$this->product->load('suppliers');
			foreach ($this->product->suppliers as $supplier) {
				$this->pivotStatus[(int) $supplier->id] = $supplier->pivot->status instanceof ProductSupplierStatus
					? $supplier->pivot->status->value
					: (string) $supplier->pivot->status;
				$this->pivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
			}

			$this->loadAttributeValues();
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'attributes', 'suppliers'];
		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
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
			'color' => ['nullable', 'string', 'max:64'],
			'unit' => ['nullable', 'string', 'max:64'],
			'characteristics' => ['nullable', 'string'],
			'photo' => ['nullable', 'image', 'max:4096'],
		], [
			'name.unique' => __('common.product_name_already_exists'),
		]);

		$photoPath = $this->product?->photo_path;
		if ($this->photo instanceof TemporaryUploadedFile) {
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
				'color' => $this->color,
				'unit' => $this->unit,
				'characteristics' => $this->characteristics,
				'photo_path' => $photoPath,
			]
		);

		$this->product = $product;
		$this->syncAttributeValues();

		session()->flash('status', __('common.product_saved'));

		$this->redirectRoute('admin.products.index');
	}

	public function updatedPhoto(): void
	{
		$this->validateOnly('photo', [
			'photo' => ['nullable', 'image', 'max:4096'],
		]);
	}

	public function removePhoto(): void
	{
		if ($this->photo instanceof TemporaryUploadedFile) {
			$this->photo = null;
			return;
		}

		if (!$this->product?->exists || !$this->product->photo_path) {
			return;
		}

		Storage::disk('public')->delete($this->product->photo_path);
		$this->product->update(['photo_path' => null]);
		$this->product->refresh();

		session()->flash('status', __('common.photo_removed'));
	}

	public function createAttributeDefinition(): void
	{
		$this->validate([
			'newAttributeLabel' => ['required', 'string', 'max:120'],
			'newAttributeCode' => ['nullable', 'string', 'max:64', 'regex:/^[a-zA-Z0-9_\\-]+$/'],
			'newAttributeType' => ['required', 'in:text'],
		], [
			'newAttributeCode.regex' => 'Code: only letters, digits, "_" and "-".',
		]);

		$companyId = $this->resolveAttributeCompanyId();

		$baseCode = trim($this->newAttributeCode);
		if ($baseCode === '') {
			$baseCode = Str::slug($this->newAttributeLabel, '_');
		}
		if ($baseCode === '') {
			$baseCode = 'attr';
		}

		$code = $baseCode;
		$i = 2;
		while ($this->attributeCodeExists($companyId, $code)) {
			$code = $baseCode.'_'.$i;
			$i++;
		}

		ProductAttributeDefinition::query()->create([
			'company_id' => $companyId,
			'code' => Str::lower($code),
			'label' => trim($this->newAttributeLabel),
			'field_type' => $this->newAttributeType,
			'options_json' => null,
			'is_active' => true,
		]);

		$this->newAttributeLabel = '';
		$this->newAttributeCode = '';
		$this->newAttributeType = 'text';

		$this->dispatch('attributes-saved');
	}

	public function deleteAttributeDefinition(int $definitionId): void
	{
		$definition = $this->attributeDefinitions()
			->firstWhere('id', $definitionId);

		if ($definition === null) {
			return;
		}

		$definition->delete();
		unset($this->attributeValues[$definitionId]);

		$this->dispatch('attributes-saved');
	}

	public function saveAttributes(): void
	{
		$this->validate([
			'attributeValues.*' => ['nullable', 'string'],
		]);

		if (!$this->product?->exists) {
			session()->flash('status', __('common.product_saved'));
			return;
		}

		$this->syncAttributeValues();

		$this->dispatch('attributes-saved');
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
			'attributeDefinitions' => $this->attributeDefinitions(),
		]);
	}

	private function resolveAttributeCompanyId(): ?int
	{
		if ($this->product?->exists) {
			return $this->product->company_id;
		}

		if (CompanyContext::isAdmin()) {
			return $this->company_id;
		}

		return CompanyContext::companyId();
	}

	private function attributeCodeExists(?int $companyId, string $code): bool
	{
		return ProductAttributeDefinition::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId), fn ($q) => $q->whereNull('company_id'))
			->where('code', Str::lower($code))
			->exists();
	}

	/**
	 * @return Collection<int, ProductAttributeDefinition>
	 */
	private function attributeDefinitions(): Collection
	{
		$companyId = $this->resolveAttributeCompanyId();

		return ProductAttributeDefinition::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId), fn ($q) => $q->whereNull('company_id'))
			->orderBy('sort_order')
			->orderBy('label')
			->get();
	}

	private function loadAttributeValues(): void
	{
		if (!$this->product?->exists) {
			$this->attributeValues = [];
			return;
		}

		$this->attributeValues = $this->product->attributeValues()
			->pluck('value_text', 'product_attribute_definition_id')
			->map(fn ($value): string => (string) $value)
			->toArray();
	}

	private function syncAttributeValues(): void
	{
		if (!$this->product?->exists) {
			return;
		}

		$definitions = $this->attributeDefinitions();
		$definitionIds = $definitions->pluck('id');

		foreach ($definitions as $definition) {
			$value = trim((string) ($this->attributeValues[$definition->id] ?? ''));

			if ($value === '') {
				$this->product->attributeValues()
					->where('product_attribute_definition_id', $definition->id)
					->delete();
				continue;
			}

			$this->product->attributeValues()->updateOrCreate(
				['product_attribute_definition_id' => $definition->id],
				['value_text' => $value, 'value_json' => null]
			);
		}

		$this->product->attributeValues()
			->when($definitionIds->isNotEmpty(), fn ($q) => $q->whereNotIn('product_attribute_definition_id', $definitionIds))
			->delete();
	}
}


