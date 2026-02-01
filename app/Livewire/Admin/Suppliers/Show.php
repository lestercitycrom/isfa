<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Supplier $supplier;

	public function mount(Supplier $supplier): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null && (int) $supplier->company_id !== $companyId) {
			abort(403);
		}

		$this->supplier = $supplier->load([
			'products' => function ($q) use ($companyId): void {
				if ($companyId !== null) {
					$q->where('products.company_id', $companyId);
				}

				$q->with('category');
			},
		]);
	}

	public function render(): View
	{
		return view('livewire.admin.suppliers.show');
	}
}
