<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CsvExportController;
use App\Http\Controllers\Admin\CsvImportController;
use App\Livewire\Admin\ActivityLogs\Index as ActivityLogsIndex;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\Companies\Edit as CompaniesEdit;
use App\Livewire\Admin\Companies\Index as CompaniesIndex;
use App\Livewire\Admin\Companies\Show as CompaniesShow;
use App\Livewire\Admin\ImportExport\Index as ImportExportIndex;
use App\Livewire\Admin\Products\Edit as ProductEdit;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use App\Livewire\Admin\Products\Show as ProductShow;
use App\Livewire\Admin\Suppliers\Edit as SupplierEdit;
use App\Livewire\Admin\Suppliers\Index as SuppliersIndex;
use App\Livewire\Admin\Suppliers\Show as SupplierShow;
use App\Livewire\Admin\Tenders\Create as TenderCreate;
use App\Livewire\Admin\Tenders\Index as TendersIndex;
use App\Livewire\Admin\Tenders\Show as TenderShow;
use Illuminate\Support\Facades\Route;

/**
 * Public entrypoint:
 * - Guest: redirect to login
 * - Authenticated: redirect to admin products
 */
Route::get('/', function () {
	if (auth()->check()) {
		return redirect()->route('admin.products.index');
	}

	return redirect()->route('login');
})->name('home');

// Auth routes
Route::middleware('guest')->group(function () {
	Route::livewire('login', App\Livewire\Auth\Login::class)->name('login');
});

Route::middleware('auth')->group(function () {
	Route::post('/logout', function () {
		auth()->logout();
		session()->invalidate();
		session()->regenerateToken();
		return redirect('/');
	})->name('logout');
});

require __DIR__ . '/settings.php';

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function (): void {
	Route::get('/', static fn () => redirect()->route('admin.products.index'))->name('home');

	Route::middleware('auth')->get('/dashboard', static function () {
		return redirect()->route('admin.products.index');
	})->name('dashboard');

	Route::get('/categories', CategoriesIndex::class)->name('categories.index');

	Route::get('/products', ProductsIndex::class)->name('products.index');
	Route::get('/products/create', ProductEdit::class)->name('products.create');
	Route::get('/products/{product}/show', ProductShow::class)->name('products.show');
	Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');

	Route::get('/suppliers', SuppliersIndex::class)->name('suppliers.index');
	Route::get('/suppliers/create', SupplierEdit::class)->name('suppliers.create');
	Route::get('/suppliers/{supplier}/show', SupplierShow::class)->name('suppliers.show');
	Route::get('/suppliers/{supplier}/edit', SupplierEdit::class)->name('suppliers.edit');

	// eTender pages
	Route::get('/tenders', TendersIndex::class)->name('tenders.index');
	Route::get('/tenders/create', TenderCreate::class)->name('tenders.create');
	Route::get('/tenders/{tender}/show', TenderShow::class)->name('tenders.show');

	Route::get('/import-export', ImportExportIndex::class)->name('import_export.index');
	Route::get('/activity-logs', ActivityLogsIndex::class)->name('activity_logs.index');

	Route::get('/export/categories', [CsvExportController::class, 'categories'])->name('export.categories');
	Route::get('/export/products', [CsvExportController::class, 'products'])->name('export.products');
	Route::get('/export/suppliers', [CsvExportController::class, 'suppliers'])->name('export.suppliers');
	Route::get('/export/links', [CsvExportController::class, 'links'])->name('export.links');

	Route::post('/import/categories', [CsvImportController::class, 'importCategories'])->name('import.categories');
	Route::post('/import/products', [CsvImportController::class, 'importProducts'])->name('import.products');
	Route::post('/import/suppliers', [CsvImportController::class, 'importSuppliers'])->name('import.suppliers');
	Route::post('/import/links', [CsvImportController::class, 'importLinks'])->name('import.links');

	Route::middleware('admin')->group(function (): void {
		Route::get('/companies', CompaniesIndex::class)->name('companies.index');
		Route::get('/companies/create', CompaniesEdit::class)->name('companies.create');
		Route::get('/companies/{company}/show', CompaniesShow::class)->name('companies.show');
		Route::get('/companies/{company}/edit', CompaniesEdit::class)->name('companies.edit');
	});
});
