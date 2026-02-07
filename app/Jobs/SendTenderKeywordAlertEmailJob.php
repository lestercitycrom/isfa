<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TenderKeywordAlertMail;
use App\Models\TenderKeywordDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendTenderKeywordAlertEmailJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public function __construct(
        public readonly int $deliveryId
    ) {}

    public function handle(): void
    {
        $delivery = TenderKeywordDelivery::query()
            ->with('subscription')
            ->find($this->deliveryId);

        if ($delivery === null || $delivery->subscription === null) {
            return;
        }

        if ($delivery->status === 'sent') {
            return;
        }

        $keyword = (string) $delivery->subscription->keyword;
        $title = (string) ($delivery->event_title ?? '');
        $url = (string) ($delivery->event_url ?? '');

        $subject = 'New tender for keyword: '.$keyword;
        $body = "Keyword: {$keyword}\n\n".($title !== '' ? $title."\n" : '').$url;

        try {
            Mail::to($delivery->recipient_email)->send(new TenderKeywordAlertMail($subject, $body));

            $delivery->update([
                'status' => 'sent',
                'sent_at' => now(),
                'error_message' => null,
            ]);
        } catch (Throwable $e) {
            $delivery->update([
                'status' => 'failed',
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
            ]);

            throw $e;
        }
    }
}
