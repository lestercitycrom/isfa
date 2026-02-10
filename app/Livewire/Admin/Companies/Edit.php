<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	use InteractsWithNotifications;

	public ?Company $company = null;

	public string $company_name = '';
	public ?string $legal_name = null;
	public ?string $tax_id = null;
	public ?string $registration_number = null;
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $address = null;
	public ?string $website = null;
	public ?string $notes = null;

	public string $account_name = '';
	public string $email = '';
	public string $password = '';

	public string $account_email = '';
	public string $account_password = '';

	/**
	 * @var array<int, bool>
	 */
	public array $accountReminderFlags = [];

	public function mount(?Company $company = null): void
	{
		$this->company = $company;

		if ($company !== null) {
			$this->company_name = $company->name ?? '';
			$this->legal_name = $company->legal_name;
			$this->tax_id = $company->tax_id;
			$this->registration_number = $company->registration_number;
			$this->contact_name = $company->contact_name;
			$this->phone = $company->phone;
			$this->address = $company->address;
			$this->website = $company->website;
			$this->notes = $company->notes;
			$this->syncAccountReminderFlags();
		}
	}

	public function save(): void
	{
		$rules = [
			'company_name' => ['required', 'string', 'max:255'],
			'legal_name' => ['nullable', 'string', 'max:255'],
			'tax_id' => ['nullable', 'string', 'max:255'],
			'registration_number' => ['nullable', 'string', 'max:255'],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'address' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
			'notes' => ['nullable', 'string'],
		];

		if (!$this->company?->exists) {
			$rules['account_name'] = ['required', 'string', 'max:255'];
			$rules['email'] = [
				'required',
				'email',
				'max:255',
				Rule::unique('users', 'email'),
			];
			$rules['password'] = ['required', 'string', 'min:6', 'max:255'];
		}

		$this->validate($rules);

		$payload = [
			'name' => $this->company_name,
			'legal_name' => $this->legal_name,
			'tax_id' => $this->tax_id,
			'registration_number' => $this->registration_number,
			'contact_name' => $this->contact_name,
			'phone' => $this->phone,
			'address' => $this->address,
			'website' => $this->website,
			'notes' => $this->notes,
		];

		if ($this->company?->exists) {
			$this->company->update($payload);
		} else {
			DB::transaction(function () use ($payload): void {
				$company = Company::query()->create($payload);
				$company->users()->create([
					'name' => $this->account_name,
					'email' => $this->email,
					'password' => $this->password,
					'password_plain' => $this->password,
					'role' => User::ROLE_COMPANY,
				]);
			});
		}

		$this->flashSuccessToast(__('common.company_saved'));

		$this->redirectRoute('admin.companies.index');
	}

	public function addAccount(): void
	{
		if (!$this->company?->exists) {
			return;
		}

		$this->validate([
			'account_name' => ['required', 'string', 'max:255'],
			'account_email' => [
				'required',
				'email',
				'max:255',
				Rule::unique('users', 'email'),
			],
			'account_password' => ['required', 'string', 'min:6', 'max:255'],
		]);

		$this->company->users()->create([
			'name' => $this->account_name,
			'email' => $this->account_email,
			'password' => $this->account_password,
			'password_plain' => $this->account_password,
			'role' => User::ROLE_COMPANY,
		]);

		$this->company->refresh()->load('users');
		$this->reset('account_name', 'account_email', 'account_password');
		$this->syncAccountReminderFlags();

		$this->notifySuccess(__('common.account_saved'));
	}

	public function deleteAccount(int $id): void
	{
		if (!$this->company?->exists) {
			return;
		}

		$user = $this->company->users()->whereKey($id)->first();
		if (!$user) {
			return;
		}

		$user->delete();
		$this->company->refresh()->load('users');
		$this->syncAccountReminderFlags();
		$this->notifySuccess(__('common.account_deleted'));
	}

	public function updatedAccountReminderFlags(mixed $value, mixed $key): void
	{
		if (!$this->company?->exists) {
			return;
		}

		$userId = (int) $key;

		$user = $this->company->users()->whereKey($userId)->first();
		if ($user === null) {
			return;
		}

		$user->update([
			'receive_tender_reminders' => (bool) $value,
		]);
	}

	public function render(): View
	{
		return view('livewire.admin.companies.edit');
	}

	private function syncAccountReminderFlags(): void
	{
		if (!$this->company?->exists) {
			$this->accountReminderFlags = [];

			return;
		}

		$this->company->refresh()->load('users');

		$this->accountReminderFlags = $this->company->users
			->mapWithKeys(fn (User $user): array => [(int) $user->id => (bool) $user->receive_tender_reminders])
			->all();
	}
}
