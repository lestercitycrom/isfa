<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?Supplier $supplier = null;
	public ?int $company_id = null;

	public string $name = '';
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $email = null;
	public ?string $website = null;
	public ?string $comment = null;

	public function mount(?Supplier $supplier = null): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$this->supplier = $supplier;

		if ($supplier !== null) {
			if (!$isAdmin && $companyId !== null && (int) $supplier->company_id !== $companyId) {
				abort(403);
			}

			$this->company_id = $supplier->company_id;
			$this->name = $supplier->name;
			$this->contact_name = $supplier->contact_name;
			$this->phone = $supplier->phone;
			$this->email = $supplier->email;
			$this->website = $supplier->website;
			$this->comment = $supplier->comment;
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function save(): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}

		$uniqueNameRule = Rule::unique('suppliers', 'name')
			->ignore($this->supplier?->id)
			->where(function ($query): void {
				if ($this->company_id !== null) {
					$query->where('company_id', $this->company_id);
				} else {
					$query->whereNull('company_id');
				}
			});

		$this->validate([
			'company_id' => ['nullable', 'integer', 'exists:users,id'],
			'name' => ['required', 'string', 'max:255', $uniqueNameRule],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'email' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
			'comment' => ['nullable', 'string'],
		], [
			'name.unique' => __('common.supplier_name_already_exists'),
		]);

		$supplier = Supplier::query()->updateOrCreate(
			['id' => $this->supplier?->id],
			[
				'company_id' => $this->company_id,
				'name' => $this->name,
				'contact_name' => $this->contact_name,
				'phone' => $this->phone,
				'email' => $this->email,
				'website' => $this->website,
				'comment' => $this->comment,
			]
		);

		session()->flash('status', __('common.supplier_saved'));

		$this->redirectRoute('admin.suppliers.index');
	}

	public function render(): View
	{
		$isAdmin = CompanyContext::isAdmin();

		return view('livewire.admin.suppliers.edit', [
			'companies' => $isAdmin ? User::query()->companies()->orderBy('company_name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
