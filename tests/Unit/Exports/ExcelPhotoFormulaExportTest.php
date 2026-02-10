<?php

use App\Exports\ProductsExport;
use App\Exports\SuppliersExport;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;

it('maps suppliers photo placeholder column for excel export', function (): void {
	$export = new SuppliersExport(null);
	$supplier = new Supplier([
		'id' => 9,
		'name' => 'Supplier 1',
		'contact_name' => 'John',
		'phone' => '12345',
		'email' => 'john@example.com',
		'website' => 'https://supplier.example.com',
		'comment' => 'Comment',
		'photo_path' => 'suppliers/photo.jpg',
	]);

	expect($export->headings())->toBe([
		'ID',
		'Sekil',
		'Techizatci adi',
		'VOEN',
		'Elaqedar sexs',
		'Telefon',
		'Email',
		'Veb sayt',
		'Odenis novu',
		'Kart nomresi',
		'Routing nomresi',
		'Rekvizitlər',
		'Qeyd',
	]);

	$mapped = $export->map($supplier);

	expect($mapped[1])->toBeNull();
});

it('maps products photo placeholder column for excel export', function (): void {
	$export = new ProductsExport(null);
	$category = new ProductCategory(['name' => 'Category']);
	$product = new Product([
		'id' => 3,
		'name' => 'Product',
		'description' => 'Description',
		'photo_path' => 'products/image.png',
	]);
	$product->setRelation('category', $category);

	expect($export->headings())->toBe([
		'ID',
		'Sekil',
		'Kateqoriya',
		'Mehsul adi',
		'Tesvir',
		'Reng',
		'Olcu vahidi',
		'Xususiyyetler',
	]);

	$mapped = $export->map($product);

	expect($mapped[1])->toBeNull();
});

it('keeps photo cells empty when photo path is missing', function (): void {
	$suppliersExport = new SuppliersExport(null);
	$productsExport = new ProductsExport(null);

	$supplier = new Supplier([
		'id' => 10,
		'name' => 'Supplier 2',
		'photo_path' => null,
	]);

	$product = new Product([
		'id' => 11,
		'name' => 'Product 2',
		'photo_path' => null,
	]);

	$supplierMapped = $suppliersExport->map($supplier);
	$productMapped = $productsExport->map($product);

	expect($supplierMapped[1])->toBeNull();
	expect($productMapped[1])->toBeNull();
});
