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
	public string $tab = 'details';
	public ?string $comment = null;

	public function mount(User $company): void
	{
		if ($company->role !== User::ROLE_COMPANY) {
			abort(404);
		}

		$this->company = $company;
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

	public function render(): View
	{
		return view('livewire.admin.companies.show');
	}
}
