<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
final class Index extends Component
{
	use WithPagination;

	public string $search = '';

	public function updatedSearch(): void
	{
		$this->resetPage();
	}

	public function delete(int $id): void
	{
		User::query()->companies()->whereKey($id)->delete();
		session()->flash('status', __('common.company_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companies = User::query()
			->companies()
			->when($this->search !== '', function ($q): void {
				$q->where(function ($q): void {
					$q->where('company_name', 'like', '%' . $this->search . '%')
						->orWhere('name', 'like', '%' . $this->search . '%')
						->orWhere('email', 'like', '%' . $this->search . '%')
						->orWhere('tax_id', 'like', '%' . $this->search . '%');
				});
			})
			->orderBy('company_name')
			->paginate(15);

		return view('livewire.admin.companies.index', [
			'companies' => $companies,
		]);
	}
}
