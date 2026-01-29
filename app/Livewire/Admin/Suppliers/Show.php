<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Supplier $supplier;

	public function mount(Supplier $supplier): void
	{
		$this->supplier = $supplier->load('products.category');
	}

	public function render(): View
	{
		return view('livewire.admin.suppliers.show');
	}
}
