<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
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

	/**
	 * Mount the component.
	 */
	public function mount(): void
	{
		$this->name = Auth::user()->name;
		$this->email = Auth::user()->email;
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

	/**
	 * Send an email verification notification to the current user.
	 */
	public function resendVerificationNotification(): void
	{
		$user = Auth::user();

		if ($user->hasVerifiedEmail()) {
			$this->redirect(route('admin.products.index'));

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
		return view('pages.settings.profile');
	}
}
