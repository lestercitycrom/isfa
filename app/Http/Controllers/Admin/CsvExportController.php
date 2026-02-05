<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
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
			fputcsv($handle, ['id', 'category_name', 'name', 'description']);

			Product::query()
				->with('category')
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(500, static function ($products) use ($handle): void {
					foreach ($products as $product) {
						fputcsv($handle, [
							$product->id,
							$product->category?->name,
							$product->name,
							$product->description,
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
			fputcsv($handle, ['id', 'name', 'contact_name', 'phone', 'email', 'website', 'comment']);

			Supplier::query()
				->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
				->orderBy('id')
				->chunk(500, static function ($suppliers) use ($handle): void {
					foreach ($suppliers as $supplier) {
						fputcsv($handle, [
							$supplier->id,
							$supplier->name,
							$supplier->contact_name,
							$supplier->phone,
							$supplier->email,
							$supplier->website,
							$supplier->comment,
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

	/**
	 * @return array<string, string>
	 */
	private function csvHeaders(): array
	{
		return [
			'Content-Type' => 'text/csv; charset=UTF-8',
		];
	}
}
