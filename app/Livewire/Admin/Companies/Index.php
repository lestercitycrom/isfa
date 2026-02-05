<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Companies;

use App\Models\Company;
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
		$company = Company::query()->with('users')->find($id);
		if (!$company) {
			return;
		}

		$company->users()->delete();
		$company->delete();
		session()->flash('status', __('common.company_deleted'));
		$this->resetPage();
	}

	public function render(): View
	{
		$companies = Company::query()
			->when($this->search !== '', function ($q): void {
				$q->where(function ($q): void {
					$q->where('name', 'like', '%' . $this->search . '%')
						->orWhere('legal_name', 'like', '%' . $this->search . '%')
						->orWhere('tax_id', 'like', '%' . $this->search . '%');
				});
			})
			->with(['users' => fn ($query) => $query->orderBy('id')])
			->withCount('users')
			->orderBy('name')
			->paginate(15);

		return view('livewire.admin.companies.index', [
			'companies' => $companies,
		]);
	}
}
