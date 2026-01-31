<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Models\Tender;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
final class Create extends Component
{
	public string $eventId = '';

	public bool $isSyncing = false;

	public ?string $lastOutput = null;

	public function sync(): void
	{
		$this->resetErrorBag();

		$validated = $this->validate([
			'eventId' => ['required', 'integer', 'min:1'],
		]);

		$eventId = (int) $validated['eventId'];

		$this->isSyncing = true;
		$this->lastOutput = null;

		try {
			// Run existing console sync to avoid duplicating parser logic
			$exitCode = Artisan::call('etender:sync-event', [
				'eventId' => $eventId,
			]);

			$this->lastOutput = trim((string) Artisan::output());

			if ($exitCode !== 0) {
				$this->addError('eventId', 'Sync failed (non-zero exit code). Check logs/output.');
				return;
			}

			$tender = Tender::query()
				->where('event_id', $eventId)
				->first();

			if ($tender === null) {
				// Fallback: in case event_id is primary key or mapped differently
				$tender = Tender::query()->whereKey($eventId)->first();
			}

			if ($tender === null) {
				$this->addError('eventId', 'Sync finished, but tender was not found in DB.');
				return;
			}

			session()->flash('status', 'Tender synced successfully.');

			$this->redirectRoute('admin.tenders.show', ['tender' => $tender]);
		} catch (Throwable $e) {
			report($e);

			$this->addError('eventId', 'Unexpected error during sync. Check logs.');
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
