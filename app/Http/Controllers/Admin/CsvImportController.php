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

				Product::query()->updateOrCreate(
					['id' => (int) ($data['id'] ?? 0) ?: null],
					[
						'category_id' => $categoryId,
						'name' => $name,
						'description' => $data['description'] ?? null,
					]
				);
			}
		});

		return redirect()
			->back()
			->with('status', 'Products imported.');
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

				Supplier::query()->updateOrCreate(
					['id' => (int) ($data['id'] ?? 0) ?: null],
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
			->with('status', 'Suppliers imported.');
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
			->with('status', 'Links imported.');
	}
}
