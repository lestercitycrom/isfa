<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendTenderKeywordAlertEmailJob;
use App\Models\TenderKeywordDelivery;
use App\Models\TenderKeywordSubscription;
use App\Services\Etender\EtenderSearchService;
use Illuminate\Console\Command;

final class TenderSendKeywordAlertsCommand extends Command
{
    protected $signature = 'tender:send-keyword-alerts';

    protected $description = 'Check eTender by keyword subscriptions and queue email alerts';

    public function handle(EtenderSearchService $searchService): int
    {
        $subscriptions = TenderKeywordSubscription::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($subscriptions->isEmpty()) {
            $this->info('No active keyword subscriptions.');

            return self::SUCCESS;
        }

        $candidates = $searchService->fetchCandidates();
        $queued = 0;

        foreach ($subscriptions as $subscription) {
            $keyword = trim((string) $subscription->keyword);
            if ($keyword === '') {
                continue;
            }

            $matches = $searchService->findMatches($keyword, $candidates);

            foreach ($matches as $item) {
                if (! is_array($item)) {
                    continue;
                }

                $eventId = $searchService->extractEventId($item);
                if ($eventId === null) {
                    continue;
                }

                $delivery = TenderKeywordDelivery::query()->firstOrCreate(
                    [
                        'subscription_id' => $subscription->id,
                        'event_id' => $eventId,
                    ],
                    [
                        'company_id' => $subscription->company_id,
                        'event_title' => $searchService->extractDisplayTitle($item),
                        'event_url' => $searchService->buildDetailUrl($eventId),
                        'recipient_email' => $subscription->email,
                        'status' => 'queued',
                        'payload' => $item,
                    ]
                );

                if (! $delivery->wasRecentlyCreated) {
                    continue;
                }

                SendTenderKeywordAlertEmailJob::dispatch((int) $delivery->id);
                $queued++;
            }

            $subscription->update(['last_checked_at' => now()]);
        }

        $this->info('Queued keyword alerts: '.$queued);

        return self::SUCCESS;
    }
}
