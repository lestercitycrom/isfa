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

		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;
		$added = 0;
		$skipped = 0;

		DB::transaction(function () use ($csv, &$header, $companyId, &$added, &$skipped): void {
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
						['name' => $categoryName, 'company_id' => $companyId],
						['description' => null, 'company_id' => $companyId]
					)->id;
				}

				$productId = (int) ($data['id'] ?? 0) ?: null;

				// Find or create product
				// If ID is provided, use it; otherwise try to find by name to merge duplicates
				$product = null;
				if ($productId !== null && $productId > 0) {
					$product = Product::query()
						->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
						->whereKey($productId)
						->first();
				}

				// If not found by ID, try to find by name + category to merge duplicates
				if ($product === null) {
					$product = Product::query()
						->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
						->where('name', $name)
						->when($categoryId !== null, fn ($q) => $q->where('category_id', $categoryId))
						->when($categoryId === null, fn ($q) => $q->whereNull('category_id'))
						->first();
				}

				// If still not found, create new product
				if ($product === null) {
					$product = Product::query()->create([
						'company_id' => $companyId,
						'category_id' => $categoryId,
						'name' => $name,
						'description' => $data['description'] ?? null,
					]);
					$added++;
				} else {
					// Update existing product (merge category and description if provided) — считаем как пропуск дубля
					$updateData = [];
					if ($categoryId !== null) {
						$updateData['category_id'] = $categoryId;
					}
					if (isset($data['description']) && $data['description'] !== null && $data['description'] !== '') {
						$updateData['description'] = $data['description'];
					}
					if ($companyId !== null) {
						$updateData['company_id'] = $companyId;
					}
					if (!empty($updateData)) {
						$product->update($updateData);
					}
					$skipped++;
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
						$supplierName = null;

						// Try to find by ID if numeric (в рамках текущего пользователя)
						if (is_numeric($supplierItem)) {
							$supplier = Supplier::query()
								->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
								->whereKey((int) $supplierItem)
								->first();
							if ($supplier === null) {
								// Поставщика с таким ID нет у пользователя — берём имя из глобальной записи и создаём своего
								$existing = Supplier::query()->whereKey((int) $supplierItem)->first();
								$supplierName = $existing?->name;
							}
						}

						// If not found by ID, try to find by name (в рамках текущего пользователя)
						if ($supplier === null) {
							$supplier = Supplier::query()
								->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
								->where('name', $supplierItem)
								->first();
							if ($supplier === null) {
								$supplierName = $supplierName ?? $supplierItem;
							}
						}

						// Если у пользователя такого поставщика нет — создаём ему поставщика с этим именем
						if ($supplier === null && $supplierName !== null && $supplierName !== '') {
							$supplier = Supplier::query()->firstOrCreate(
								['name' => $supplierName, 'company_id' => $companyId],
								['company_id' => $companyId]
							);
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
			->with('status', __('common.products_import_result', [
				'added' => $added,
				'skipped' => $skipped,
			]));
	}

	public function importSuppliers(Request $request): RedirectResponse
	{
		$request->validate([
			'file' => ['required', 'file', 'mimes:csv,txt'],
		]);

		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header, $companyId): void {
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

				if ($supplierId !== null && $companyId !== null) {
					$owned = Supplier::query()
						->whereKey($supplierId)
						->where('company_id', $companyId)
						->exists();

					if (!$owned) {
						$supplierId = null;
					}
				}

				// If no valid ID provided, prefer existing supplier with same name inside the company.
				if ($supplierId === null) {
					$existing = Supplier::query()
						->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
						->when($companyId === null, fn ($q) => $q->whereNull('company_id'))
						->where('name', $name)
						->first();
					if ($existing !== null) {
						$supplierId = $existing->id;
					}
				}

				Supplier::query()->updateOrCreate(
					['id' => $supplierId],
					[
						'company_id' => $companyId,
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

		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header, $companyId): void {
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

				if ($categoryId !== null && $companyId !== null) {
					$owned = ProductCategory::query()
						->whereKey($categoryId)
						->where('company_id', $companyId)
						->exists();

					if (!$owned) {
						$categoryId = null;
					}
				}

				// Check for duplicate name if creating new category
				if ($categoryId === null) {
					$existing = ProductCategory::query()
						->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
						->where('name', $name)
						->first();
					if ($existing !== null) {
						continue; // Skip duplicate
					}
				}

				ProductCategory::query()->updateOrCreate(
					['id' => $categoryId],
					[
						'company_id' => $companyId,
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

		$companyId = auth()->user()?->isAdmin() ? null : auth()->user()?->company_id;

		$filePath = $request->file('file')?->getRealPath();

		if ($filePath === false || $filePath === null) {
			throw new RuntimeException('Cannot read uploaded file.');
		}

		$csv = new SplFileObject($filePath);
		$csv->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY);

		$header = null;

		DB::transaction(static function () use ($csv, &$header, $companyId): void {
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

				$product = Product::query()
					->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
					->whereKey($productId)
					->first();
				$supplier = Supplier::query()
					->when($companyId !== null, fn ($q) => $q->where('company_id', $companyId))
					->whereKey($supplierId)
					->first();

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
