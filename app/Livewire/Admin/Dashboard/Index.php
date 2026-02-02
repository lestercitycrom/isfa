<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Dashboard;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\Tender;
use App\Models\DictionaryValue;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Spatie\Activitylog\Models\Activity;

#[Layout('layouts.admin')]
final class Index extends Component
{
	public function render(): View
	{
		$companyId = CompanyContext::companyId();

		$productsCount = Product::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->count();

		$suppliersCount = Supplier::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->count();

		$categoriesCount = ProductCategory::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->count();

		$tendersCount = Tender::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->count();

		$tendersLast30Days = Tender::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->whereRaw('COALESCE(`published_at`, `created_at`) >= DATE_SUB(NOW(), INTERVAL 30 DAY)')
			->count();

		$linkedProductsCount = DB::table('products')
			->join('tender_product', 'products.id', '=', 'tender_product.product_id')
			->when($companyId !== null, fn ($q) => $q->where('products.company_id', $companyId))
			->distinct('products.id')
			->count('products.id');

		$coverage = $productsCount > 0
			? (int) round(($linkedProductsCount / $productsCount) * 100)
			: 0;

		$topCategories = ProductCategory::query()
			->leftJoin('products', 'products.category_id', '=', 'product_categories.id')
			->when($companyId !== null, fn ($q) => $q->where('product_categories.company_id', $companyId))
			->selectRaw('product_categories.id, product_categories.name, COUNT(products.id) as products_count')
			->groupBy('product_categories.id', 'product_categories.name')
			->orderByDesc('products_count')
			->limit(6)
			->get();

		$topSuppliers = Supplier::query()
			->leftJoin('product_supplier', 'suppliers.id', '=', 'product_supplier.supplier_id')
			->when($companyId !== null, fn ($q) => $q->where('suppliers.company_id', $companyId))
			->selectRaw('suppliers.id, suppliers.name, COUNT(product_supplier.product_id) as links_count')
			->groupBy('suppliers.id', 'suppliers.name')
			->orderByDesc('links_count')
			->limit(6)
			->get();

		$statusStats = Tender::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->selectRaw('COALESCE(event_status_code, "") as status_code, COUNT(*) as total')
			->groupBy('status_code')
			->orderByDesc('total')
			->limit(6)
			->get();

		$statusLabels = DictionaryValue::query()
			->where('dictionary', 'event_status')
			->pluck('label', 'code')
			->toArray();

		$statusTotal = (int) $statusStats->sum('total');

		$latestActivities = Activity::query()
			->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
			->latest()
			->limit(10)
			->get();

		return view('livewire.admin.dashboard.index', [
			'isAdmin' => CompanyContext::isAdmin(),
			'productsCount' => $productsCount,
			'suppliersCount' => $suppliersCount,
			'categoriesCount' => $categoriesCount,
			'tendersCount' => $tendersCount,
			'tendersLast30Days' => $tendersLast30Days,
			'linkedProductsCount' => $linkedProductsCount,
			'coverage' => $coverage,
			'topCategories' => $topCategories,
			'topSuppliers' => $topSuppliers,
			'statusStats' => $statusStats,
			'statusLabels' => $statusLabels,
			'statusTotal' => $statusTotal,
			'latestActivities' => $latestActivities,
		]);
	}
}
