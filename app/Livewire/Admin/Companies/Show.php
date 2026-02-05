<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Company $company;
	public string $tab = 'details';
	public ?string $comment = null;

	public string $account_name = '';
	public string $account_email = '';
	public string $account_password = '';

	public function mount(Company $company): void
	{
		$this->company = $company->load('users');
		$this->comment = $company->notes;
	}

	public function setTab(string $tab): void
	{
		$allowed = ['details', 'comments'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'details';
	}

	public function saveComment(): void
	{
		$this->company->update([
			'notes' => $this->comment,
		]);

		$this->dispatch('comment-saved');
	}

	public function addAccount(): void
	{
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

		session()->flash('status', __('common.account_saved'));
	}

	public function deleteAccount(int $id): void
	{
		$user = $this->company->users()->whereKey($id)->first();
		if (!$user) {
			return;
		}

		$user->delete();
		$this->company->refresh()->load('users');
		session()->flash('status', __('common.account_deleted'));
	}

	public function render(): View
	{
		return view('livewire.admin.companies.show', [
			'accounts' => $this->company->users,
		]);
	}
}
