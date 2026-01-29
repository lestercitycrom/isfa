<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function render(): View
	{
		$products = Product::query()
			->with('category')
			->when($this->search !== '', function ($q): void {
				$q->where('name', 'like', '%' . $this->search . '%');
			})
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.products.index', [
			'products' => $products,
		]);
	}
}
