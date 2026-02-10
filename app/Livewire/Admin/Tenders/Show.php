<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Enums\ProductSupplierStatus;
use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\DictionaryValue;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Tag;
use App\Models\Tender;
use App\Models\TenderItem;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
final class Show extends Component
{
    use WithFileUploads;
    use InteractsWithNotifications;

    public Tender $tender;

    public string $tab = 'details';

    public string $productSearch = '';

    public int $attachProductId = 0;

    public bool $showProductDropdown = false;

    public ?string $comment = null;

    public int $selectedItemId = 0;

    public mixed $itemPhoto = null;

    public int $attachSupplierId = 0;

    public string $attachStatus = 'primary';

    public ?string $attachTerms = null;

    public string $supplierSearch = '';

    public bool $showSupplierDropdown = false;

    public string $tagInput = '';

    public bool $showTagDropdown = false;

    /**
     * @var array<int, string>
     */
    public array $itemPivotStatus = [];

    /**
     * @var array<int, string|null>
     */
    public array $itemPivotTerms = [];

    /**
     * @var array<string, array<string, string>>
     */
    public array $dictLabels = [];

    public function mount(Tender $tender): void
    {
        $companyId = CompanyContext::companyId();
        $isAdmin = CompanyContext::isAdmin();

        if (! $isAdmin && $companyId !== null && (int) $tender->company_id !== $companyId) {
            abort(403);
        }

        $this->tender = $tender->load([
            'items.suppliers',
            'contacts',
            'announcements',
            'publishHistories',
            'products.category',
            'products.suppliers',
            'products.company',
            'tags',
        ]);

        $this->comment = $this->tender->comment;

        $this->dictLabels = $this->buildDictionaryLabelMaps([
            'event_type',
            'event_status',
            'document_view_type',
        ]);

        $this->selectedItemId = (int) ($this->tender->items->first()?->id ?? 0);
        $this->hydrateItemSupplierPivot();
    }

    public function render(): View
    {
        return view('livewire.admin.tenders.show', [
            'originalUrl' => 'https://etender.gov.az/main/competition/detail/'.$this->tender->event_id,
            'activities' => $this->loadActivities(),
            'productOptions' => $this->productOptions(),
            'supplierOptions' => $this->supplierOptions(),
            'selectedItem' => $this->selectedItem(),
            'tagSuggestions' => $this->tagSuggestions(),
            'isAdmin' => CompanyContext::isAdmin(),
        ]);
    }

    public function setTab(string $tab): void
    {
        $allowed = ['details', 'history', 'comments', 'item-suppliers'];

        $this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
    }

    public function attachProduct(int $productId): void
    {
        $isAdmin = CompanyContext::isAdmin();
        $companyId = $this->tender->company_id !== null ? (int) $this->tender->company_id : null;

        $productQuery = Product::query()->whereKey($productId);
        if ($companyId !== null) {
            $productQuery->where('company_id', $companyId);
        } elseif ($isAdmin) {
            $productQuery->whereNull('company_id');
        } else {
            return;
        }

        $product = $productQuery->firstOrFail();

        if ($this->tender->products()->whereKey($productId)->exists()) {
            return;
        }

        $this->tender->products()->attach($productId, [
            'company_id' => $companyId ?? $product->company_id,
        ]);

        activity()
            ->performedOn($this->tender)
            ->causedBy(CompanyContext::user())
            ->event('attached')
            ->withProperties([
                'product_id' => $product->id,
                'product_name' => $product->name,
            ])
            ->log('Product attached');

        $this->productSearch = '';
        $this->showProductDropdown = false;
        $this->tender->load('products.category');
        $this->tender->load('products.suppliers', 'products.company');
        $this->notifySuccess(__('common.product_attached'));
    }

    public function attachSelectedProduct(): void
    {
        if ($this->attachProductId === 0) {
            $first = $this->productOptions()->first();
            if ($first !== null) {
                $this->attachProductId = (int) $first->id;
            }
        }

        if ($this->attachProductId > 0) {
            $this->attachProduct($this->attachProductId);
            $this->attachProductId = 0;
        }
    }

    public function attachFromSearch(): void
    {
        $this->attachSelectedProduct();
    }

    public function updatedProductSearch(): void
    {
        $this->attachProductId = 0;
        $this->showProductDropdown = true;
    }

    public function updatedSelectedItemId(): void
    {
        $this->itemPhoto = null;
        $this->resetItemSupplierForm();
        $this->hydrateItemSupplierPivot();
    }

    public function updatedItemPhoto(): void
    {
        $this->validateOnly('itemPhoto', [
            'itemPhoto' => ['nullable', 'image', 'max:4096'],
        ]);
    }

    public function saveItemPhoto(): void
    {
        $item = $this->selectedItem();

        if (! $item || ! ($this->itemPhoto instanceof TemporaryUploadedFile)) {
            return;
        }

        $photoPath = $item->photo_path;
        if ($photoPath) {
            Storage::disk('public')->delete($photoPath);
        }

        $photoPath = $this->itemPhoto->store('tender-items', 'public');
        $item->update(['photo_path' => $photoPath]);
        $this->itemPhoto = null;

        $this->tender->load('items.suppliers');
        $this->notifySuccess(__('common.saved'));
    }

    public function removeItemPhoto(): void
    {
        if ($this->itemPhoto instanceof TemporaryUploadedFile) {
            $this->itemPhoto = null;

            return;
        }

        $item = $this->selectedItem();
        if (! $item || ! $item->photo_path) {
            return;
        }

        Storage::disk('public')->delete($item->photo_path);
        $item->update(['photo_path' => null]);
        $this->tender->load('items.suppliers');

        $this->notifySuccess(__('common.photo_removed'));
    }

    public function updatedSupplierSearch(): void
    {
        $this->attachSupplierId = 0;
        $this->showSupplierDropdown = true;
    }

    public function updatedTagInput(): void
    {
        $this->showTagDropdown = true;
    }

    public function addTagFromInput(): void
    {
        $name = trim(preg_replace('/\s+/u', ' ', $this->tagInput) ?? '');
        if ($name === '') {
            return;
        }

        $slug = Str::slug($name);
        if ($slug === '') {
            $slug = Str::lower(str_replace(' ', '-', $name));
        }

        if ($slug === '') {
            return;
        }

        $tag = Tag::query()->firstOrCreate(
            [
                'company_id' => $this->tagCompanyId(),
                'slug' => $slug,
            ],
            [
                'name' => $name,
            ]
        );

        $this->tender->tags()->syncWithoutDetaching([$tag->id]);
        $this->tender->load('tags');
        $this->tagInput = '';
        $this->showTagDropdown = false;
    }

    public function selectTag(int $tagId): void
    {
        $exists = Tag::query()
            ->whereKey($tagId)
            ->where(function ($query): void {
                $companyId = $this->tagCompanyId();

                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->exists();

        if (! $exists) {
            return;
        }

        $this->tender->tags()->syncWithoutDetaching([$tagId]);
        $this->tender->load('tags');
        $this->tagInput = '';
        $this->showTagDropdown = false;
    }

    public function removeTag(int $tagId): void
    {
        $this->tender->tags()->detach($tagId);
        $this->tender->load('tags');
    }

    public function selectSupplier(int $supplierId): void
    {
        $supplier = $this->supplierOptions()
            ->first(fn (Supplier $item) => (int) $item->id === $supplierId);

        if ($supplier === null) {
            return;
        }

        $this->attachSupplierId = $supplierId;
        $this->supplierSearch = $supplier->name.' (#'.$supplier->id.')';
        $this->showSupplierDropdown = false;
    }

    public function attachItemSupplier(): void
    {
        $item = $this->selectedItem();

        if (! $item) {
            return;
        }

        if ($this->attachSupplierId === 0) {
            $first = $this->supplierOptions()->first();
            if ($first !== null) {
                $this->attachSupplierId = (int) $first->id;
            }
        }

        $companyId = $this->tender->company_id;
        $existsRule = Rule::exists('suppliers', 'id');
        if ($companyId !== null) {
            $existsRule->where('company_id', $companyId);
        }

        $this->validate([
            'attachSupplierId' => ['required', 'integer', $existsRule],
            'attachStatus' => ['required', 'in:primary,reserve'],
            'attachTerms' => ['nullable', 'string'],
        ]);

        $item->suppliers()->syncWithoutDetaching([
            $this->attachSupplierId => [
                'status' => $this->attachStatus,
                'terms' => $this->attachTerms,
            ],
        ]);

        $this->tender->load('items.suppliers');
        $this->resetItemSupplierForm();
        $this->hydrateItemSupplierPivot();

        $this->notifySuccess(__('common.supplier_linked'));
    }

    public function saveItemSupplierPivot(int $supplierId): void
    {
        $item = $this->selectedItem();

        if (! $item) {
            return;
        }

        $status = ProductSupplierStatus::tryFrom($this->itemPivotStatus[$supplierId] ?? 'primary')
            ?? ProductSupplierStatus::Primary;

        $item->suppliers()->updateExistingPivot($supplierId, [
            'status' => $status->value,
            'terms' => $this->itemPivotTerms[$supplierId] ?? null,
        ]);

        $this->tender->load('items.suppliers');
        $this->hydrateItemSupplierPivot();
        $this->notifySuccess(__('common.link_updated'));
    }

    public function detachItemSupplier(int $supplierId): void
    {
        $item = $this->selectedItem();

        if (! $item) {
            return;
        }

        $item->suppliers()->detach($supplierId);
        unset($this->itemPivotStatus[$supplierId], $this->itemPivotTerms[$supplierId]);

        $this->tender->load('items.suppliers');
        $this->hydrateItemSupplierPivot();
        $this->notifySuccess(__('common.supplier_unlinked'));
    }

    public function selectProduct(int $productId): void
    {
        $product = $this->productOptions()
            ->first(fn (Product $item) => (int) $item->id === $productId);

        if ($product === null) {
            return;
        }

        $this->attachProductId = $productId;
        $this->productSearch = $product->name.' (#'.$product->id.')';
        $this->showProductDropdown = false;
    }

    public function detachProduct(int $productId): void
    {
        $companyId = $this->tender->company_id !== null ? (int) $this->tender->company_id : null;

        if ($companyId === null) {
            return;
        }

        $product = $this->tender->products()->whereKey($productId)->first();

        if (! $product) {
            return;
        }

        $this->tender->products()->detach($productId);

        activity()
            ->performedOn($this->tender)
            ->causedBy(CompanyContext::user())
            ->event('detached')
            ->withProperties([
                'product_id' => $product->id,
                'product_name' => $product->name,
            ])
            ->log('Product detached');

        $this->tender->load('products.category');
        $this->tender->load('products.suppliers', 'products.company');
        $this->notifySuccess(__('common.product_detached'));
    }

    public function saveComment(): void
    {
        if (! Schema::hasColumn($this->tender->getTable(), 'comment')) {
            $raw = is_array($this->tender->raw) ? $this->tender->raw : [];
            $raw['comment'] = $this->comment;
            $this->tender->update(['raw' => $raw]);
            $this->dispatch('comment-saved');
            $this->notifySuccess(__('common.saved'));

            return;
        }

        $this->tender->update([
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
        return Activity::query()
            ->where('subject_type', $this->tender->getMorphClass())
            ->where('subject_id', $this->tender->getKey())
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    private function productOptions(): Collection
    {
        $search = trim($this->productSearch);

        $companyId = $this->tender->company_id !== null ? (int) $this->tender->company_id : null;

        if ($companyId === null && ! CompanyContext::isAdmin()) {
            return collect();
        }

        $query = Product::query()
            ->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
            ->when($companyId === null && CompanyContext::isAdmin(), fn ($q) => $q->whereNull('company_id'))
            ->whereDoesntHave('tenders', function ($query): void {
                $query->whereKey($this->tender->getKey());
            })
            ->with('category')
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->limit(20)->get();
    }

    /**
     * @return Collection<int, Supplier>
     */
    private function supplierOptions(): Collection
    {
        $item = $this->selectedItem();

        if (! $item) {
            return collect();
        }

        $search = trim($this->supplierSearch);

        $query = Supplier::query()
            ->when($this->tender->company_id !== null, fn ($q) => $q->where('company_id', $this->tender->company_id))
            ->whereDoesntHave('tenderItems', function ($query) use ($item): void {
                $query->whereKey($item->id);
            })
            ->orderBy('name');

        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->limit(20)->get();
    }

    private function selectedItem(): ?TenderItem
    {
        if ($this->selectedItemId <= 0) {
            return null;
        }

        $item = $this->tender->items->firstWhere('id', $this->selectedItemId);

        return $item instanceof TenderItem ? $item : null;
    }

    private function resetItemSupplierForm(): void
    {
        $this->attachSupplierId = 0;
        $this->attachStatus = 'primary';
        $this->attachTerms = null;
        $this->supplierSearch = '';
        $this->showSupplierDropdown = false;
    }

    private function hydrateItemSupplierPivot(): void
    {
        $this->itemPivotStatus = [];
        $this->itemPivotTerms = [];

        $item = $this->selectedItem();
        if (! $item) {
            return;
        }

        foreach ($item->suppliers as $supplier) {
            $this->itemPivotStatus[(int) $supplier->id] = $supplier->pivot->status instanceof ProductSupplierStatus
                ? $supplier->pivot->status->value
                : (string) $supplier->pivot->status;
            $this->itemPivotTerms[(int) $supplier->id] = $supplier->pivot->terms;
        }
    }

    /**
     * @return Collection<int, Tag>
     */
    private function tagSuggestions(): Collection
    {
        $selectedIds = $this->tender->tags->pluck('id')->map(static fn (mixed $id): int => (int) $id)->all();

        $query = Tag::query()
            ->where(function ($query): void {
                $companyId = $this->tagCompanyId();

                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->whereNotIn('id', $selectedIds)
            ->orderBy('name');

        $search = trim($this->tagInput);
        if ($search !== '') {
            $query->where('name', 'like', '%'.$search.'%');
        }

        return $query->limit(8)->get();
    }

    /**
     * @param  array<int, string>  $dictionaries
     * @return array<string, array<string, string>>
     */
    private function buildDictionaryLabelMaps(array $dictionaries): array
    {
        $rows = DictionaryValue::query()
            ->whereIn('dictionary', $dictionaries)
            ->orderBy('dictionary')
            ->orderBy('code')
            ->get();

        $maps = [];

        foreach ($rows as $row) {
            $dict = (string) $row->dictionary;
            $code = (string) $row->code;
            $label = $row->label !== null && trim((string) $row->label) !== ''
                ? (string) $row->label
                : $code;

            $maps[$dict][$code] = $label;
        }

        return $maps;
    }

    private function tagCompanyId(): ?int
    {
        return $this->tender->company_id !== null ? (int) $this->tender->company_id : null;
    }
}
