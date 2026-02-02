<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Product;
use App\Models\Tender;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Tender $tender;

	public string $tab = 'details';

	public string $productSearch = '';
	public int $attachProductId = 0;
	public bool $showProductDropdown = false;
	public ?string $comment = null;

	/**
	 * @var array<string, array<string, string>>
	 */
	public array $dictLabels = [];

	public function mount(Tender $tender): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $tender->company_id !== $companyId) {
			abort(403);
		}

		$this->tender = $tender->load([
			'items',
			'contacts',
			'announcements',
			'publishHistories',
			'products.category',
		]);

		$this->comment = $this->tender->comment;

		$this->dictLabels = $this->buildDictionaryLabelMaps([
			'event_type',
			'event_status',
			'document_view_type',
		]);
	}

	public function render(): View
	{
		return view('livewire.admin.tenders.show', [
			'originalUrl' => 'https://etender.gov.az/main/competition/detail/' . $this->tender->event_id,
			'activities' => $this->loadActivities(),
			'productOptions' => $this->productOptions(),
		]);
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'products', 'history', 'comments'];

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
		session()->flash('status', __('common.product_attached'));
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

	public function selectProduct(int $productId): void
	{
		$product = $this->productOptions()
			->first(fn (Product $item) => (int) $item->id === $productId);

		if ($product === null) {
			return;
		}

		$this->attachProductId = $productId;
		$this->productSearch = $product->name . ' (#' . $product->id . ')';
		$this->showProductDropdown = false;
	}

	public function detachProduct(int $productId): void
	{
		$companyId = $this->tender->company_id !== null ? (int) $this->tender->company_id : null;

		if ($companyId === null) {
			return;
		}

		$product = $this->tender->products()->whereKey($productId)->first();

		if (!$product) {
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
		session()->flash('status', __('common.product_detached'));
	}

	public function saveComment(): void
	{
		if (!Schema::hasColumn($this->tender->getTable(), 'comment')) {
			$raw = is_array($this->tender->raw) ? $this->tender->raw : [];
			$raw['comment'] = $this->comment;
			$this->tender->update(['raw' => $raw]);
			$this->dispatch('comment-saved');

			return;
		}

		$this->tender->update([
			'comment' => $this->comment,
		]);

		$this->dispatch('comment-saved');
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

		if ($companyId === null && !CompanyContext::isAdmin()) {
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
			$query->where('name', 'like', '%' . $search . '%');
		}

		return $query->limit(20)->get();
	}

	/**
	 * @param array<int, string> $dictionaries
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
}
