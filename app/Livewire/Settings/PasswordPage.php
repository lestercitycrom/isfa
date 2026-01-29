<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class PasswordPage extends Component
{
	use PasswordValidationRules;

	public string $current_password = '';
	public string $password = '';
	public string $password_confirmation = '';

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

		session()->flash('status', __('common.password_updated'));
	}

	public function render()
	{
		return view('pages.settings.password');
	}
}
