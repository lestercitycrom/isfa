<?php

declare(strict_types=1);

use App\Http\Controllers\Admin\CsvExportController;
use App\Http\Controllers\Admin\CsvImportController;
use App\Livewire\Admin\Categories\Index as CategoriesIndex;
use App\Livewire\Admin\ImportExport\Index as ImportExportIndex;
use App\Livewire\Admin\Products\Edit as ProductEdit;
use App\Livewire\Admin\Products\Index as ProductsIndex;
use App\Livewire\Admin\Products\Show as ProductShow;
use App\Livewire\Admin\Suppliers\Edit as SupplierEdit;
use App\Livewire\Admin\Suppliers\Index as SuppliersIndex;
use App\Livewire\Admin\Suppliers\Show as SupplierShow;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

require __DIR__.'/settings.php';

Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function (): void {
	Route::get('/', static fn () => redirect()->route('admin.products.index'))->name('home');

	Route::get('/categories', CategoriesIndex::class)->name('categories.index');

	Route::get('/products', ProductsIndex::class)->name('products.index');
	Route::get('/products/create', ProductEdit::class)->name('products.create');
	Route::get('/products/{product}/show', ProductShow::class)->name('products.show');
	Route::get('/products/{product}/edit', ProductEdit::class)->name('products.edit');

	Route::get('/suppliers', SuppliersIndex::class)->name('suppliers.index');
	Route::get('/suppliers/create', SupplierEdit::class)->name('suppliers.create');
	Route::get('/suppliers/{supplier}/show', SupplierShow::class)->name('suppliers.show');
	Route::get('/suppliers/{supplier}/edit', SupplierEdit::class)->name('suppliers.edit');

	Route::get('/import-export', ImportExportIndex::class)->name('import_export.index');

	Route::get('/export/products', [CsvExportController::class, 'products'])->name('export.products');
	Route::get('/export/suppliers', [CsvExportController::class, 'suppliers'])->name('export.suppliers');
	Route::get('/export/links', [CsvExportController::class, 'links'])->name('export.links');

	Route::post('/import/products', [CsvImportController::class, 'importProducts'])->name('import.products');
	Route::post('/import/suppliers', [CsvImportController::class, 'importSuppliers'])->name('import.suppliers');
	Route::post('/import/links', [CsvImportController::class, 'importLinks'])->name('import.links');
});
