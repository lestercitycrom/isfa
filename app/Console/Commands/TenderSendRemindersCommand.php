<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\SendTenderReminderEmailJob;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\Tender;
use App\Models\User;
use App\Services\Notifications\TenderReminderTemplateRenderer;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

final class TenderSendRemindersCommand extends Command
{
	protected $signature = 'tender:send-reminders {--date= : Date in Y-m-d format for dry run targeting}';
	protected $description = 'Queue reminder emails for tenders with deadlines in 7, 3 and 1 days';

	public function handle(TenderReminderTemplateRenderer $renderer): int
	{
		$today = $this->resolveToday();

		$templateByDays = [
			7 => 'tender_reminder_7d',
			3 => 'tender_reminder_3d',
			1 => 'tender_reminder_1d',
		];

		$templates = NotificationTemplate::query()
			->whereIn('key', array_values($templateByDays))
			->where('is_active', true)
			->get()
			->keyBy('key');

		$queued = 0;
		$processedTenders = 0;

		Tender::query()
			->whereNotNull('company_id')
			->where(function ($query): void {
				$query->whereNotNull('end_at')
					->orWhereNotNull('envelope_at');
			})
			->chunkById(200, function ($tenders) use (
				$today,
				$templateByDays,
				$templates,
				$renderer,
				&$queued,
				&$processedTenders
			): void {
				foreach ($tenders as $tender) {
					$processedTenders++;

					$deadline = $tender->end_at ?? $tender->envelope_at;
					if ($deadline === null) {
						continue;
					}

					$daysLeft = (int) $today->diffInDays($deadline->toImmutable()->startOfDay(), false);
					if (!isset($templateByDays[$daysLeft])) {
						continue;
					}

					$templateKey = $templateByDays[$daysLeft];
					$template = $templates->get($templateKey);
					if ($template === null) {
						continue;
					}

					$variables = $renderer->buildVariables($tender, $daysLeft);
					$subject = $renderer->render((string) $template->subject, $variables);
					$body = $renderer->render((string) $template->body, $variables);

					User::query()
						->where('company_id', $tender->company_id)
						->where('receive_tender_reminders', true)
						->whereNotNull('email')
						->chunkById(200, function ($users) use (
							$tender,
							$templateKey,
							$daysLeft,
							$subject,
							$body,
							&$queued
						): void {
							foreach ($users as $user) {
								$delivery = NotificationDelivery::query()->firstOrCreate(
									[
										'user_id' => $user->id,
										'tender_id' => $tender->id,
										'reminder_type' => $daysLeft . 'd',
									],
									[
										'company_id' => $tender->company_id,
										'template_key' => $templateKey,
										'recipient_email' => (string) $user->email,
										'subject' => $subject,
										'body' => $body,
										'deadline_at' => $tender->end_at ?? $tender->envelope_at,
										'status' => 'queued',
									]
								);

								if (!$delivery->wasRecentlyCreated) {
									continue;
								}

								SendTenderReminderEmailJob::dispatch((int) $delivery->id);
								$queued++;
							}
						});
				}
			});

		$this->info("Processed tenders: {$processedTenders}");
		$this->info("Queued reminders: {$queued}");

		return self::SUCCESS;
	}

	private function resolveToday(): CarbonImmutable
	{
		$tz = (string) config('etender.timezone', 'Asia/Baku');
		$raw = trim((string) $this->option('date'));

		if ($raw !== '') {
			try {
				return CarbonImmutable::createFromFormat('Y-m-d', $raw, $tz)->startOfDay();
			} catch (\Throwable) {
				$this->warn("Invalid --date '{$raw}', using current date.");
			}
		}

		return now($tz)->toImmutable()->startOfDay();
	}
}
