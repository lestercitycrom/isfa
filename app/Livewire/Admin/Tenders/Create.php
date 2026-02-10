<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Services\Etender\EtenderEventSyncService;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
final class Create extends Component
{
	use InteractsWithNotifications;

	public string $eventId = '';

	public bool $isSyncing = false;

	public ?string $lastOutput = null;

	public function sync(EtenderEventSyncService $syncService): void
	{
		$this->resetErrorBag();

		$validated = $this->validate([
			'eventId' => ['required', 'integer', 'min:1'],
		]);

		$eventId = (int) $validated['eventId'];

		$this->isSyncing = true;
		$this->lastOutput = null;

		try {
			$tender = $syncService->sync($eventId, CompanyContext::companyId());

			$this->flashSuccessToast(__('tenders.flash.synced', ['id' => $tender->event_id]));

			$this->redirectRoute('admin.tenders.show', ['tender' => $tender]);
		} catch (Throwable $e) {
			report($e);

			$this->addError('eventId', __('tenders.errors.unexpected_error'));
			$this->lastOutput = $this->lastOutput ?: ($e->getMessage() ?: 'Unknown error');
		} finally {
			$this->isSyncing = false;
		}
	}

	public function render(): View
	{
		return view('livewire.admin.tenders.create');
	}
}
