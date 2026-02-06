<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Suppliers;

use App\Models\Supplier;
use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
final class Edit extends Component
{
	use WithFileUploads;

	public ?Supplier $supplier = null;
	public ?int $company_id = null;

	public string $name = '';
	public ?string $voen = null;
	public ?string $contact_name = null;
	public ?string $phone = null;
	public ?string $email = null;
	public ?string $website = null;
	public string $payment_method = 'cash';
	public ?string $payment_card_number = null;
	public ?string $payment_routing_number = null;
	public mixed $photo = null;
	public ?string $comment = null;

	public function mount(?Supplier $supplier = null): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		$this->supplier = $supplier;

		if ($supplier !== null) {
			if (!$isAdmin && $companyId !== null && (int) $supplier->company_id !== $companyId) {
				abort(403);
			}

			$this->company_id = $supplier->company_id;
			$this->name = $supplier->name;
			$this->voen = $supplier->voen;
			$this->contact_name = $supplier->contact_name;
			$this->phone = $supplier->phone;
			$this->email = $supplier->email;
			$this->website = $supplier->website;
			$this->payment_method = $supplier->payment_method ?: 'cash';
			$this->payment_card_number = $supplier->payment_card_number;
			$this->payment_routing_number = $supplier->payment_routing_number;
			$this->comment = $supplier->comment;
		} elseif (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}
	}

	public function save(): void
	{
		$companyId = CompanyContext::companyId();
		$isAdmin = CompanyContext::isAdmin();

		if (!$isAdmin && $companyId !== null) {
			$this->company_id = $companyId;
		}

		$uniqueNameRule = Rule::unique('suppliers', 'name')
			->ignore($this->supplier?->id)
			->where(function ($query): void {
				if ($this->company_id !== null) {
					$query->where('company_id', $this->company_id);
				} else {
					$query->whereNull('company_id');
				}
			});

		$this->validate([
			'company_id' => ['nullable', 'integer', 'exists:companies,id'],
			'name' => ['required', 'string', 'max:255', $uniqueNameRule],
			'voen' => ['nullable', 'string', 'max:64'],
			'contact_name' => ['nullable', 'string', 'max:255'],
			'phone' => ['nullable', 'string', 'max:255'],
			'email' => ['nullable', 'string', 'max:255'],
			'website' => ['nullable', 'string', 'max:255'],
			'payment_method' => ['required', 'in:cash,card,kocurme'],
			'payment_card_number' => ['nullable', 'string', 'max:64', 'required_if:payment_method,card'],
			'payment_routing_number' => ['nullable', 'string', 'max:64', 'required_if:payment_method,kocurme'],
			'photo' => ['nullable', 'image', 'max:4096'],
			'comment' => ['nullable', 'string'],
		], [
			'name.unique' => __('common.supplier_name_already_exists'),
		]);

		$photoPath = $this->supplier?->photo_path;
		if ($this->photo instanceof TemporaryUploadedFile) {
			if ($photoPath) {
				Storage::disk('public')->delete($photoPath);
			}
			$photoPath = $this->photo->store('suppliers', 'public');
		}

		$supplier = Supplier::query()->updateOrCreate(
			['id' => $this->supplier?->id],
			[
				'company_id' => $this->company_id,
				'name' => $this->name,
				'voen' => $this->voen,
				'contact_name' => $this->contact_name,
				'phone' => $this->phone,
				'email' => $this->email,
				'website' => $this->website,
				'payment_method' => $this->payment_method,
				'payment_card_number' => $this->payment_method === 'card' ? $this->payment_card_number : null,
				'payment_routing_number' => $this->payment_method === 'kocurme' ? $this->payment_routing_number : null,
				'photo_path' => $photoPath,
				'comment' => $this->comment,
			]
		);

		session()->flash('status', __('common.supplier_saved'));

		$this->redirectRoute('admin.suppliers.index');
	}

	public function updatedPhoto(): void
	{
		$this->validateOnly('photo', [
			'photo' => ['nullable', 'image', 'max:4096'],
		]);
	}

	public function updatedPaymentMethod(): void
	{
		if ($this->payment_method !== 'card') {
			$this->payment_card_number = null;
		}

		if ($this->payment_method !== 'kocurme') {
			$this->payment_routing_number = null;
		}
	}

	public function removePhoto(): void
	{
		if ($this->photo instanceof TemporaryUploadedFile) {
			$this->photo = null;
			return;
		}

		if (!$this->supplier?->exists || !$this->supplier->photo_path) {
			return;
		}

		Storage::disk('public')->delete($this->supplier->photo_path);
		$this->supplier->update(['photo_path' => null]);
		$this->supplier->refresh();

		session()->flash('status', __('common.photo_removed'));
	}

	public function render(): View
	{
		$isAdmin = CompanyContext::isAdmin();

		return view('livewire.admin.suppliers.edit', [
			'companies' => $isAdmin ? Company::query()->orderBy('name')->get() : collect(),
			'isAdmin' => $isAdmin,
		]);
	}
}
