<?php

declare(strict_types=1);

use App\Livewire\Admin\Settings\Notifications;
use App\Mail\TenderReminderMail;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;

it('sends test email from notification settings page', function (): void {
	config()->set('activitylog.enabled', false);
	Mail::fake();

	$admin = User::factory()->create([
		'role' => User::ROLE_ADMIN,
		'email' => 'admin@example.com',
	]);

	$this->actingAs($admin);

	Livewire::test(Notifications::class)
		->set('testEmail', 'qa@example.com')
		->set('testTemplateKey', 'tender_reminder_7d')
		->call('sendTestEmail')
		->assertHasNoErrors();

	Mail::assertSent(TenderReminderMail::class, function (TenderReminderMail $mail): bool {
		return $mail->hasTo('qa@example.com');
	});
});
