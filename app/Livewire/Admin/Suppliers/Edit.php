<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	public ?Supplier $supplier = null;

	public string $name = '';
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $email = null;
	public ?string $website = null;
	public ?string $comment = null;

	public function mount(?Supplier $supplier = null): void
	{
		$this->supplier = $supplier;

		if ($supplier !== null) {
			$this->name = $supplier->name;
			$this->contact_name = $supplier->contact_name;
			$this->phone = $supplier->phone;
			$this->email = $supplier->email;
			$this->website = $supplier->website;
			$this->comment = $supplier->comment;
		}
	}

	public function save(): void
	{
		$this->validate([
			'name' => ['required', 'string', 'max:255'],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'email' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
			'comment' => ['nullable', 'string'],
		]);

		$supplier = Supplier::query()->updateOrCreate(
			['id' => $this->supplier?->id],
			[
				'name' => $this->name,
				'contact_name' => $this->contact_name,
				'phone' => $this->phone,
				'email' => $this->email,
				'website' => $this->website,
				'comment' => $this->comment,
			]
		);

		session()->flash('status', 'Supplier saved.');

		$this->redirectRoute('admin.suppliers.show', ['supplier' => $supplier->id]);
	}

	public function render(): View
	{
		return view('livewire.admin.suppliers.edit');
	}
}
