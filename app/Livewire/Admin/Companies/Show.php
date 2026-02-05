<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Show extends Component
{
	public Company $company;
	public string $tab = 'details';
	public ?string $comment = null;

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

	public function render(): View
	{
		return view('livewire.admin.companies.show', [
			'accounts' => $this->company->users,
		]);
	}
}
