<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?User $company = null;

	public string $company_name = '';
	public ?string $legal_name = null;
	public ?string $tax_id = null;
	public ?string $registration_number = null;
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $address = null;
	public ?string $website = null;
	public ?string $notes = null;

	public string $email = '';
	public string $password = '';

	public function mount(?User $company = null): void
	{
		if ($company !== null && $company->role !== User::ROLE_COMPANY) {
			abort(404);
		}

		$this->company = $company;

		if ($company !== null) {
			$this->company_name = $company->company_name ?? $company->name ?? '';
			$this->legal_name = $company->legal_name;
			$this->tax_id = $company->tax_id;
			$this->registration_number = $company->registration_number;
			$this->contact_name = $company->contact_name;
			$this->phone = $company->phone;
			$this->address = $company->address;
			$this->website = $company->website;
			$this->notes = $company->notes;
			$this->email = $company->email ?? '';
			$this->password = (string) ($company->password_plain ?? '');
		}
	}

	public function save(): void
	{
		$this->validate([
			'company_name' => ['required', 'string', 'max:255'],
			'legal_name' => ['nullable', 'string', 'max:255'],
			'tax_id' => ['nullable', 'string', 'max:255'],
			'registration_number' => ['nullable', 'string', 'max:255'],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'address' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
			'notes' => ['nullable', 'string'],
			'email' => [
				'required',
				'email',
				'max:255',
				Rule::unique('users', 'email')->ignore($this->company?->id),
			],
			'password' => [
				$this->company ? 'nullable' : 'required',
				'string',
				'min:6',
				'max:255',
			],
		]);

		$payload = [
			'role' => User::ROLE_COMPANY,
			'name' => $this->company_name,
			'company_name' => $this->company_name,
			'legal_name' => $this->legal_name,
			'tax_id' => $this->tax_id,
			'registration_number' => $this->registration_number,
			'contact_name' => $this->contact_name,
			'phone' => $this->phone,
			'address' => $this->address,
			'website' => $this->website,
			'notes' => $this->notes,
			'email' => $this->email,
		];

		if ($this->password !== '') {
			$payload['password'] = Hash::make($this->password);
			$payload['password_plain'] = $this->password;
		}

		User::query()->updateOrCreate(
			['id' => $this->company?->id],
			$payload
		);

		session()->flash('status', __('common.company_saved'));

		$this->redirectRoute('admin.companies.index');
	}

	public function render(): View
	{
		return view('livewire.admin.companies.edit');
	}
}
