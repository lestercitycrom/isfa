<?php

namespace App\Console\Commands;

use App\Services\Etender\EtenderEventSyncService;
use Illuminate\Console\Command;
use Throwable;

class EtenderSyncEventCommand extends Command
{
	protected $signature = 'etender:sync-event {eventId : etender.gov.az event id} {--company= : Company user id}';
	protected $description = 'Sync a single eTender event into local database';

	public function handle(EtenderEventSyncService $service): int
	{
		$eventId = (int) $this->argument('eventId');
		$companyId = $this->option('company') !== null ? (int) $this->option('company') : null;

		try {
			$tender = $service->sync($eventId, $companyId);
		} catch (Throwable $e) {
			$this->error($e->getMessage());
			return self::FAILURE;
		}

		$this->info("Synced tender: event_id={$tender->event_id}, id={$tender->id}");
		return self::SUCCESS;
	}
}
