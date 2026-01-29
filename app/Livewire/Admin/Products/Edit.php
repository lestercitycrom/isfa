<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?Product $product = null;

	public ?int $category_id = null;
	public string $name = '';
	public ?string $description = null;

	public function mount(?Product $product = null): void
	{
		$this->product = $product;

		if ($product !== null) {
			$this->category_id = $product->category_id;
			$this->name = $product->name;
			$this->description = $product->description;
		}
	}

	public function save(): void
	{
		$this->validate([
			'category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
			'name' => ['required', 'string', 'max:255'],
			'description' => ['nullable', 'string'],
		]);

		$product = Product::query()->updateOrCreate(
			['id' => $this->product?->id],
			[
				'category_id' => $this->category_id,
				'name' => $this->name,
				'description' => $this->description,
			]
		);

		session()->flash('status', 'Product saved.');

		$this->redirectRoute('admin.products.show', ['product' => $product->id]);
	}

	public function render(): View
	{
		return view('livewire.admin.products.edit', [
			'categories' => ProductCategory::query()->orderBy('name')->get(),
		]);
	}
}
