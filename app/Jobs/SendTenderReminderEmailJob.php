<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Mail\TenderReminderMail;
use App\Models\NotificationDelivery;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class SendTenderReminderEmailJob implements ShouldQueue
{
	use Dispatchable;
	use InteractsWithQueue;
	use Queueable;
	use SerializesModels;

	public int $tries = 3;

	public function __construct(
		public readonly int $deliveryId
	) {
	}

	public function handle(): void
	{
		$delivery = NotificationDelivery::query()->with('user', 'tender')->find($this->deliveryId);

		if ($delivery === null || $delivery->user === null || $delivery->tender === null) {
			return;
		}

		if ($delivery->status === 'sent') {
			return;
		}

		try {
			Mail::to($delivery->recipient_email)->send(
				new TenderReminderMail((string) $delivery->subject, (string) $delivery->body)
			);

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
