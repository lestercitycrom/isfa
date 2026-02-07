<?php

declare(strict_types=1);

use App\Jobs\SendTenderReminderEmailJob;
use App\Mail\TenderReminderMail;
use App\Models\Company;
use App\Models\NotificationDelivery;
use App\Models\Tender;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

it('queues reminders only for subscribed company users and avoids duplicates', function (): void {
	config()->set('activitylog.enabled', false);

	Queue::fake();

	$company = Company::query()->create(['name' => 'Acme LLC']);

	$subscribed = User::factory()->create([
		'company_id' => $company->id,
		'email' => 'yes@example.com',
		'receive_tender_reminders' => true,
	]);

	User::factory()->create([
		'company_id' => $company->id,
		'email' => 'no@example.com',
		'receive_tender_reminders' => false,
	]);

	$otherCompany = Company::query()->create(['name' => 'Other LLC']);
	User::factory()->create([
		'company_id' => $otherCompany->id,
		'email' => 'other@example.com',
		'receive_tender_reminders' => true,
	]);

	$tender = Tender::query()->create([
		'company_id' => $company->id,
		'event_id' => 500001,
		'title' => 'Office chairs procurement',
		'document_number' => '2026/AT/00500/V1',
		'end_at' => '2026-02-14 15:00:00',
	]);

	Artisan::call('tender:send-reminders', ['--date' => '2026-02-07']);
	Artisan::call('tender:send-reminders', ['--date' => '2026-02-07']);

	expect(NotificationDelivery::query()->count())->toBe(1);
	expect(NotificationDelivery::query()->where('user_id', $subscribed->id)->where('tender_id', $tender->id)->where('reminder_type', '7d')->exists())->toBeTrue();

	Queue::assertPushed(SendTenderReminderEmailJob::class, 1);
});

it('sends queued reminder mail and marks delivery as sent', function (): void {
	config()->set('activitylog.enabled', false);

	Mail::fake();

	$company = Company::query()->create(['name' => 'Acme LLC']);

	$user = User::factory()->create([
		'company_id' => $company->id,
		'email' => 'person@example.com',
		'receive_tender_reminders' => true,
	]);

	$tender = Tender::query()->create([
		'company_id' => $company->id,
		'event_id' => 500002,
		'title' => 'Laptop procurement',
		'document_number' => '2026/AT/00501/V1',
		'end_at' => '2026-02-20 12:00:00',
	]);

	$delivery = NotificationDelivery::query()->create([
		'company_id' => $company->id,
		'user_id' => $user->id,
		'tender_id' => $tender->id,
		'template_key' => 'tender_reminder_3d',
		'reminder_type' => '3d',
		'recipient_email' => $user->email,
		'subject' => 'Test subject',
		'body' => 'Test body',
		'status' => 'queued',
	]);

	SendTenderReminderEmailJob::dispatchSync((int) $delivery->id);

	Mail::assertSent(TenderReminderMail::class, function (TenderReminderMail $mail) use ($user): bool {
		return $mail->hasTo($user->email) && $mail->subjectLine === 'Test subject';
	});

	$delivery->refresh();

	expect($delivery->status)->toBe('sent');
	expect($delivery->sent_at)->not->toBeNull();
});
