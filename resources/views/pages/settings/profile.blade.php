<?php

use App\Concerns\ProfileValidationRules;
use App\Models\User;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
new class extends Component {
    use ProfileValidationRules;

    public string $name = '';
    public string $email = '';

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

        session()->flash('status', __('common.profile_updated'));
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
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<div class="space-y-6">
	<x-admin.page-header
		:title="__('common.profile')"
		:subtitle="__('common.profile_subtitle')"
	/>

	<x-admin.card>
		<form wire:submit="updateProfileInformation" class="space-y-6">
			<x-admin.input
				:label="__('common.name')"
				type="text"
				wire:model="name"
				required
				autofocus
				autocomplete="name"
				:error="$errors->first('name')"
			/>

			<div>
				<x-admin.input
					:label="__('common.email')"
					type="email"
					wire:model="email"
					required
					autocomplete="email"
					:error="$errors->first('email')"
				/>

				@if ($this->hasUnverifiedEmail)
					<div class="mt-4">
						<x-admin.alert variant="warning">
							{{ __('common.email_unverified') }}
							<button wire:click.prevent="resendVerificationNotification" class="underline">
								{{ __('common.resend_verification') }}
							</button>
						</x-admin.alert>

						@if (session('status') === 'verification-link-sent')
							<x-admin.alert variant="success" class="mt-2">
								{{ __('common.verification_link_sent') }}
							</x-admin.alert>
						@endif
					</div>
				@endif
			</div>

			<div class="flex items-center gap-4">
				<x-admin.button variant="primary" type="submit">
					{{ __('common.save') }}
				</x-admin.button>
			</div>
		</form>
	</x-admin.card>
</div>
