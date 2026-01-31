<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Enums\ProductSupplierStatus;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;
use SplFileObject;

final class CsvImportController extends Controller
{
	public function importProducts(Request $request): RedirectResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header): void {
			foreach ($csv as $row) {
				if (!is_array($row) || count($row) === 1 && $row[0] === null) {
					continue;
				}

				if ($header === null) {
					$header = array_map(static fn ($v) => Str::of((string) $v)->trim()->toString(), $row);
					continue;
				}

				$data = array_combine($header, $row);

				if (!is_array($data)) {
					continue;
				}

				$name = trim((string) ($data['name'] ?? ''));

				if ($name === '') {
					continue;
				}

				$categoryName = trim((string) ($data['category_name'] ?? ''));

				$categoryId = null;

				if ($categoryName !== '') {
					$categoryId = ProductCategory::query()->firstOrCreate(
						['name' => $categoryName],
						['description' => null]
					)->id;
				}

				$productId = (int) ($data['id'] ?? 0) ?: null;

				// Find or create product
				// If ID is provided, use it; otherwise try to find by name to merge duplicates
				$product = null;
				if ($productId !== null && $productId > 0) {
					$product = Product::query()->find($productId);
				}

				// If not found by ID, try to find by name + category to merge duplicates
				if ($product === null) {
					$product = Product::query()
						->where('name', $name)
						->when($categoryId !== null, fn ($q) => $q->where('category_id', $categoryId))
						->when($categoryId === null, fn ($q) => $q->whereNull('category_id'))
						->first();
				}

				// If still not found, create new product
				if ($product === null) {
					$product = Product::query()->create([
						'category_id' => $categoryId,
						'name' => $name,
						'description' => $data['description'] ?? null,
					]);
				} else {
					// Update existing product (merge category and description if provided)
					$updateData = [];
					if ($categoryId !== null) {
						$updateData['category_id'] = $categoryId;
					}
					if (isset($data['description']) && $data['description'] !== null && $data['description'] !== '') {
						$updateData['description'] = $data['description'];
					}
					if (!empty($updateData)) {
						$product->update($updateData);
					}
				}

				// Import supplier links if provided in CSV
				// Format: supplier_ids or supplier_names can be comma-separated
				// Can contain both IDs (numeric) and names (text) mixed together
				$supplierInput = null;
				if (isset($data['supplier_ids']) && trim((string) $data['supplier_ids']) !== '') {
					$supplierInput = trim((string) $data['supplier_ids']);
				} elseif (isset($data['supplier_names']) && trim((string) $data['supplier_names']) !== '') {
					$supplierInput = trim((string) $data['supplier_names']);
				}

				if ($supplierInput !== null && $supplierInput !== '') {
					$supplierItems = array_map('trim', explode(',', $supplierInput));
					$supplierItems = array_filter($supplierItems, fn ($item) => $item !== '');

					foreach ($supplierItems as $supplierItem) {
						$supplier = null;

						// Try to find by ID if numeric
						if (is_numeric($supplierItem)) {
							$supplier = Supplier::query()->find((int) $supplierItem);
						}

						// If not found by ID, try to find by name
						if ($supplier === null) {
							$supplier = Supplier::query()->where('name', $supplierItem)->first();
						}

						if ($supplier !== null) {
							$statusRaw = (string) ($data['status'] ?? 'reserve');
							$status = ProductSupplierStatus::tryFrom($statusRaw) ?? ProductSupplierStatus::Reserve;
							$terms = $data['terms'] ?? null;

							// Check if supplier is already linked to this product
							$existingPivot = $product->suppliers()
								->where('suppliers.id', $supplier->id)
								->first();

							if ($existingPivot !== null) {
								// Update existing link (allows different status/terms from different CSV rows)
								$product->suppliers()->updateExistingPivot($supplier->id, [
									'status' => $status->value,
									'terms' => $terms,
								]);
							} else {
								// Add new link
								$product->suppliers()->attach($supplier->id, [
									'status' => $status->value,
									'terms' => $terms,
								]);
							}
						}
					}
				}
			}
		});

		return redirect()
			->back()
			->with('status', __('common.products_imported'));
	}

	public function importSuppliers(Request $request): RedirectResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header): void {
			foreach ($csv as $row) {
				if (!is_array($row) || count($row) === 1 && $row[0] === null) {
					continue;
				}

				if ($header === null) {
					$header = array_map(static fn ($v) => Str::of((string) $v)->trim()->toString(), $row);
					continue;
				}

				$data = array_combine($header, $row);

				if (!is_array($data)) {
					continue;
				}

				$name = trim((string) ($data['name'] ?? ''));

				if ($name === '') {
					continue;
				}

				$supplierId = (int) ($data['id'] ?? 0) ?: null;

				// Check for duplicate name if creating new supplier (optional - no unique constraint in DB)
				// But we'll skip if exact duplicate exists to avoid confusion
				if ($supplierId === null) {
					$existing = Supplier::query()
						->where('name', $name)
						->where('phone', $data['phone'] ?? null)
						->where('email', $data['email'] ?? null)
						->first();
					if ($existing !== null) {
						continue; // Skip exact duplicate
					}
				}

				Supplier::query()->updateOrCreate(
					['id' => $supplierId],
					[
						'name' => $name,
						'contact_name' => $data['contact_name'] ?? null,
						'phone' => $data['phone'] ?? null,
						'email' => $data['email'] ?? null,
						'website' => $data['website'] ?? null,
						'comment' => $data['comment'] ?? null,
					]
				);
			}
		});

		return redirect()
			->back()
			->with('status', __('common.suppliers_imported'));
	}

	public function importCategories(Request $request): RedirectResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header): void {
			foreach ($csv as $row) {
				if (!is_array($row) || count($row) === 1 && $row[0] === null) {
					continue;
				}

				if ($header === null) {
					$header = array_map(static fn ($v) => Str::of((string) $v)->trim()->toString(), $row);
					continue;
				}

				$data = array_combine($header, $row);

				if (!is_array($data)) {
					continue;
				}

				$name = trim((string) ($data['name'] ?? ''));

				if ($name === '') {
					continue;
				}

				$categoryId = (int) ($data['id'] ?? 0) ?: null;

				// Check for duplicate name if creating new category
				if ($categoryId === null) {
					$existing = ProductCategory::query()->where('name', $name)->first();
					if ($existing !== null) {
						continue; // Skip duplicate
					}
				}

				ProductCategory::query()->updateOrCreate(
					['id' => $categoryId],
					[
						'name' => $name,
						'description' => $data['description'] ?? null,
					]
				);
			}
		});

		return redirect()
			->back()
			->with('status', __('common.categories_imported'));
	}

	public function importLinks(Request $request): RedirectResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header): void {
			foreach ($csv as $row) {
				if (!is_array($row) || count($row) === 1 && $row[0] === null) {
					continue;
				}

				if ($header === null) {
					$header = array_map(static fn ($v) => Str::of((string) $v)->trim()->toString(), $row);
					continue;
				}

				$data = array_combine($header, $row);

				if (!is_array($data)) {
					continue;
				}

				$productId = (int) ($data['product_id'] ?? 0);
				$supplierId = (int) ($data['supplier_id'] ?? 0);

				if ($productId <= 0 || $supplierId <= 0) {
					continue;
				}

				$statusRaw = (string) ($data['status'] ?? 'reserve');
				$status = ProductSupplierStatus::tryFrom($statusRaw) ?? ProductSupplierStatus::Reserve;

				$terms = $data['terms'] ?? null;

				$product = Product::query()->find($productId);
				$supplier = Supplier::query()->find($supplierId);

				if ($product === null || $supplier === null) {
					continue;
				}

				$product->suppliers()->syncWithoutDetaching([
					$supplierId => [
						'status' => $status->value,
						'terms' => $terms,
					],
				]);
			}
		});

		return redirect()
			->back()
			->with('status', __('common.links_imported'));
	}
}
