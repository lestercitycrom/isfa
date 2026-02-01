<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public User $company;

	public function mount(User $company): void
	{
		if ($company->role !== User::ROLE_COMPANY) {
			abort(404);
		}

		$this->company = $company;
	}

	public function render(): View
	{
		return view('livewire.admin.companies.show');
	}
}
