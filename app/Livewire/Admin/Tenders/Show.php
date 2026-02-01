<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\DictionaryValue;
use App\Models\Product;
use App\Models\Tender;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
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
		$allowed = ['details', 'products', 'history'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
	}

	public function attachProduct(int $productId): void
	{
		$companyId = $this->tender->company_id !== null ? (int) $this->tender->company_id : null;

		if ($companyId === null) {
			return;
		}

		$product = Product::query()
			->whereKey($productId)
			->where('company_id', $companyId)
			->firstOrFail();

		if ($this->tender->products()->whereKey($productId)->exists()) {
			return;
		}

		$this->tender->products()->attach($productId, [
			'company_id' => $companyId,
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
		$this->tender->load('products.category');
		session()->flash('status', __('common.product_attached'));
	}

	public function attachSelectedProduct(): void
	{
		if ($this->attachProductId > 0) {
			$this->attachProduct($this->attachProductId);
			$this->attachProductId = 0;
		}
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

		if ($this->tender->company_id === null) {
			return collect();
		}

		$query = Product::query()
			->where('company_id', $this->tender->company_id)
			->whereDoesntHave('tenders', function ($query): void {
				$query->whereKey($this->tender->getKey());
			})
			->with('category')
			->orderBy('name');

		if ($search !== '') {
			$query->where('name', 'like', '%' . $search . '%');
		}

		return $query->limit(12)->get();
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
