<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Exports\CategoriesExport;
use App\Exports\ProductSupplierLinksExport;
use App\Exports\ProductsExport;
use App\Exports\SuppliersExport;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class CsvExportController extends Controller
{
	public function products(): StreamedResponse
	{
		$fileName = 'products.csv';
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return response()->streamDownload(function () use ($companyId): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'category_name', 'name', 'description', 'photo_url']);

			Product::query()
				->with('category')
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(500, function ($products) use ($handle): void {
					foreach ($products as $product) {
						fputcsv($handle, [
							$product->id,
							$product->category?->name,
							$product->name,
							$product->description,
							$this->photoUrl($product->photo_path),
						]);
					}
				});

			fclose($handle);
		}, $fileName, $this->csvHeaders());
	}

	public function suppliers(): StreamedResponse
	{
		$fileName = 'suppliers.csv';
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return response()->streamDownload(function () use ($companyId): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'name', 'contact_name', 'phone', 'email', 'website', 'comment', 'photo_url']);

			Supplier::query()
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(500, function ($suppliers) use ($handle): void {
					foreach ($suppliers as $supplier) {
						fputcsv($handle, [
							$supplier->id,
							$supplier->name,
							$supplier->contact_name,
							$supplier->phone,
							$supplier->email,
							$supplier->website,
							$supplier->comment,
							$this->photoUrl($supplier->photo_path),
						]);
					}
				});

			fclose($handle);
		}, $fileName, $this->csvHeaders());
	}

	public function categories(): StreamedResponse
	{
		$fileName = 'categories.csv';
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return response()->streamDownload(function () use ($companyId): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'name', 'description']);

			ProductCategory::query()
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(500, static function ($categories) use ($handle): void {
					foreach ($categories as $category) {
						fputcsv($handle, [
							$category->id,
							$category->name,
							$category->description,
						]);
					}
				});

			fclose($handle);
		}, $fileName, $this->csvHeaders());
	}

	public function links(): StreamedResponse
	{
		$fileName = 'product_supplier.csv';
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return response()->streamDownload(function () use ($companyId): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['product_id', 'supplier_id', 'status', 'terms']);

			Product::query()
				->with(['suppliers' => function ($q) use ($companyId): void {
					if ($companyId !== null) {
						$q->where('suppliers.company_id', $companyId);
					}
				}])
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(200, static function ($products) use ($handle): void {
					foreach ($products as $product) {
					foreach ($product->suppliers as $supplier) {
						$status = $supplier->pivot->status instanceof \App\Enums\ProductSupplierStatus
							? $supplier->pivot->status->value
							: (string) $supplier->pivot->status;
						
						fputcsv($handle, [
							$product->id,
							$supplier->id,
							$status,
							$supplier->pivot->terms,
						]);
					}
					}
				});

			fclose($handle);
		}, $fileName, $this->csvHeaders());
	}

	public function productsExcel(): BinaryFileResponse
	{
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return Excel::download(new ProductsExport($companyId), 'products.xlsx');
	}

	public function suppliersExcel(): BinaryFileResponse
	{
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return Excel::download(new SuppliersExport($companyId), 'suppliers.xlsx');
	}

	public function categoriesExcel(): BinaryFileResponse
	{
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return Excel::download(new CategoriesExport($companyId), 'categories.xlsx');
	}

	public function linksExcel(): BinaryFileResponse
	{
		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		return Excel::download(new ProductSupplierLinksExport($companyId), 'product_supplier.xlsx');
	}

	/**
	 * @return array<string, string>
	 */
	private function csvHeaders(): array
	{
		return [
			'Content-Type' => 'text/csv; charset=UTF-8',
		];
	}

	private function photoUrl(?string $path): ?string
	{
		if ($path === null || $path === '') {
			return null;
		}

		$base = rtrim((string) config('app.url'), '/');

		return $base . '/storage/' . ltrim($path, '/');
	}
}
