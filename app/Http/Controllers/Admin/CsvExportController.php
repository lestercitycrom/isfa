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

		return response()->streamDownload(function (): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'category_name', 'name', 'description']);

			Product::query()
				->with('category')
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

		return response()->streamDownload(function (): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'name', 'contact_name', 'phone', 'email', 'website', 'comment']);

			Supplier::query()
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

		return response()->streamDownload(function (): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['id', 'name', 'description']);

			ProductCategory::query()
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

		return response()->streamDownload(function (): void {
			$handle = fopen('php://output', 'wb');

			// Header
			fputcsv($handle, ['product_id', 'supplier_id', 'status', 'terms']);

			Product::query()
				->with('suppliers')
				->orderBy('id')
				->chunk(200, static function ($products) use ($handle): void {
					foreach ($products as $product) {
						foreach ($product->suppliers as $supplier) {
							fputcsv($handle, [
								$product->id,
								$supplier->id,
								(string) $supplier->pivot->status,
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
