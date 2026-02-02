<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class ProfilePage extends Component
{
	use ProfileValidationRules;
	use PasswordValidationRules;

	public string $name = '';
	public string $email = '';

	public string $current_password = '';
	public string $password = '';
	public string $password_confirmation = '';

	public ?string $company_name = null;
	public ?string $legal_name = null;
	public ?string $tax_id = null;
	public ?string $registration_number = null;
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $address = null;
	public ?string $website = null;

	/**
	 * Mount the component.
	 */
	public function mount(): void
	{
		$user = Auth::user();

		$this->name = $user->name;
		$this->email = $user->email;

		$this->company_name = $user->company_name;
		$this->legal_name = $user->legal_name;
		$this->tax_id = $user->tax_id;
		$this->registration_number = $user->registration_number;
		$this->contact_name = $user->contact_name;
		$this->phone = $user->phone;
		$this->address = $user->address;
		$this->website = $user->website;
	}

	/**
	 * Update the profile information for the currently authenticated user.
	 */
	public function updateProfileInformation(): void
	{
		$user = Auth::user();

		$validated = $this->validate($this->profileRules($user->id));

		$user->fill($validated);

		if ($user->isDirty('email')) {
			$user->email_verified_at = null;
		}

		$user->save();

		$this->dispatch('profile-updated', name: $user->name);
	}

	/**
	 * Update the password for the currently authenticated user.
	 */
	public function updatePassword(): void
	{
		try {
			$validated = $this->validate([
				'current_password' => $this->currentPasswordRules(),
				'password' => $this->passwordRules(),
			]);
		} catch (ValidationException $e) {
			$this->reset('current_password', 'password', 'password_confirmation');

			throw $e;
		}

		Auth::user()->update([
			'password' => $validated['password'],
		]);

		$this->reset('current_password', 'password', 'password_confirmation');

		$this->dispatch('password-updated');
	}

	public function updateCompanyInformation(): void
	{
		$user = Auth::user();

		if ($user->role !== User::ROLE_COMPANY) {
			abort(403);
		}

		$validated = $this->validate([
			'company_name' => ['required', 'string', 'max:255'],
			'legal_name' => ['nullable', 'string', 'max:255'],
			'tax_id' => ['nullable', 'string', 'max:255'],
			'registration_number' => ['nullable', 'string', 'max:255'],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'address' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
		]);

		$user->fill($validated);
		$user->name = $validated['company_name'];
		$user->company_name = $validated['company_name'];
		$user->save();

		$this->dispatch('company-updated');
	}

	/**
	 * Send an email verification notification to the current user.
	 */
	public function resendVerificationNotification(): void
	{
		$user = Auth::user();

		if ($user->hasVerifiedEmail()) {
			$this->redirect(route('admin.dashboard'));

			return;
		}

		$user->sendEmailVerificationNotification();

		Session::flash('status', 'verification-link-sent');
	}

	#[Computed]
	public function hasUnverifiedEmail(): bool
	{
		return Auth::user() instanceof MustVerifyEmail && !Auth::user()->hasVerifiedEmail();
	}

	#[Computed]
	public function showDeleteUser(): bool
	{
		return !Auth::user() instanceof MustVerifyEmail
			|| (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
	}

	public function render()
	{
		$user = Auth::user();

		return view('pages.settings.profile', [
			'isCompany' => $user->role === User::ROLE_COMPANY,
		]);
	}
}
