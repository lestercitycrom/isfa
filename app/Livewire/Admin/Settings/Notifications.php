<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Mail\TenderReminderMail;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Services\Notifications\TenderReminderTemplateRenderer;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
final class Notifications extends Component
{
	public string $tab = 'email';

	public string $subject7d = '';
	public string $body7d = '';
	public bool $active7d = true;

	public string $subject3d = '';
	public string $body3d = '';
	public bool $active3d = true;

	public string $subject1d = '';
	public string $body1d = '';
	public bool $active1d = true;

	public string $testEmail = '';
	public string $testTemplateKey = 'tender_reminder_7d';
	public ?string $testResultStatus = null; // success|error
	public ?string $testResultMessage = null;
	public ?string $testResultDetails = null;

	public function mount(): void
	{
		$this->loadTemplates();
		$this->testEmail = (string) (Auth::user()?->email ?? '');
	}

	public function setTab(string $tab): void
	{
		$allowed = ['email', 'log'];

		$this->tab = in_array($tab, $allowed, true) ? $tab : 'email';
	}

	public function saveTemplates(): void
	{
		$this->validate([
			'subject7d' => ['required', 'string', 'max:255'],
			'body7d' => ['required', 'string'],
			'subject3d' => ['required', 'string', 'max:255'],
			'body3d' => ['required', 'string'],
			'subject1d' => ['required', 'string', 'max:255'],
			'body1d' => ['required', 'string'],
		]);

		$this->saveTemplate('tender_reminder_7d', 'Tender reminder: 7 days', $this->subject7d, $this->body7d, $this->active7d);
		$this->saveTemplate('tender_reminder_3d', 'Tender reminder: 3 days', $this->subject3d, $this->body3d, $this->active3d);
		$this->saveTemplate('tender_reminder_1d', 'Tender reminder: 1 day', $this->subject1d, $this->body1d, $this->active1d);

		$this->dispatch('templates-saved');
	}

	public function sendTestEmail(TenderReminderTemplateRenderer $renderer): void
	{
		$this->validate([
			'testEmail' => ['required', 'email', 'max:255'],
			'testTemplateKey' => ['required', 'in:tender_reminder_7d,tender_reminder_3d,tender_reminder_1d'],
		]);

		$template = NotificationTemplate::query()
			->where('key', $this->testTemplateKey)
			->first();

		if ($template === null) {
			$this->addError('testTemplateKey', 'Template not found.');

			return;
		}

		$daysLeft = match ($this->testTemplateKey) {
			'tender_reminder_7d' => 7,
			'tender_reminder_3d' => 3,
			default => 1,
		};

		$vars = [
			'tender_code' => 'TEST-2026-001',
			'tender_title' => 'Test tender notification',
			'deadline_at' => now()->addDays($daysLeft)->format('Y-m-d H:i'),
			'days_left' => (string) $daysLeft,
			'tender_url' => url('/admin/tenders'),
		];

		$subject = $renderer->render((string) $template->subject, $vars);
		$body = $renderer->render((string) $template->body, $vars);

		try {
			Mail::to($this->testEmail)->send(new TenderReminderMail($subject, $body));

			$this->testResultStatus = 'success';
			$this->testResultMessage = __('common.test_email_sent_ok');
			$this->testResultDetails = sprintf(
				'%s: %s | %s: %s | %s: %s',
				(string) __('common.email'),
				$this->testEmail,
				(string) __('common.template'),
				$this->testTemplateKey,
				(string) __('common.time'),
				now()->format('Y-m-d H:i:s')
			);

			$this->dispatch('test-email-sent');
		} catch (Throwable $e) {
			Log::error('Test reminder email failed', [
				'email' => $this->testEmail,
				'template_key' => $this->testTemplateKey,
				'exception' => $e::class,
				'message' => $e->getMessage(),
			]);

			$this->testResultStatus = 'error';
			$this->testResultMessage = __('common.test_email_failed');
			$this->testResultDetails = $e->getMessage();
		}
	}

	public function render(TenderReminderTemplateRenderer $renderer): View
	{
		return view('livewire.admin.settings.notifications', [
			'availablePlaceholders' => $renderer->availablePlaceholders(),
			'placeholderDescriptions' => [
				'{{tender_code}}' => __('common.var_tender_code'),
				'{{tender_title}}' => __('common.var_tender_title'),
				'{{deadline_at}}' => __('common.var_deadline_at'),
				'{{days_left}}' => __('common.var_days_left'),
				'{{tender_url}}' => __('common.var_tender_url'),
			],
			'deliveries' => NotificationDelivery::query()
				->with(['user', 'tender'])
				->latest()
				->limit(200)
				->get(),
			'mailDefault' => (string) config('mail.default'),
			'mailHost' => (string) config('mail.mailers.smtp.host'),
			'mailPort' => (string) config('mail.mailers.smtp.port'),
			'mailFrom' => (string) config('mail.from.address'),
		]);
	}

	private function loadTemplates(): void
	{
		$templates = NotificationTemplate::query()
			->whereIn('key', ['tender_reminder_7d', 'tender_reminder_3d', 'tender_reminder_1d'])
			->get()
			->keyBy('key');

		$t7 = $templates->get('tender_reminder_7d');
		$t3 = $templates->get('tender_reminder_3d');
		$t1 = $templates->get('tender_reminder_1d');

		$this->subject7d = (string) ($t7?->subject ?? '');
		$this->body7d = (string) ($t7?->body ?? '');
		$this->active7d = (bool) ($t7?->is_active ?? true);

		$this->subject3d = (string) ($t3?->subject ?? '');
		$this->body3d = (string) ($t3?->body ?? '');
		$this->active3d = (bool) ($t3?->is_active ?? true);

		$this->subject1d = (string) ($t1?->subject ?? '');
		$this->body1d = (string) ($t1?->body ?? '');
		$this->active1d = (bool) ($t1?->is_active ?? true);
	}

	private function saveTemplate(string $key, string $name, string $subject, string $body, bool $isActive): void
	{
		NotificationTemplate::query()->updateOrCreate(
			['key' => $key],
			[
				'name' => $name,
				'subject' => $subject,
				'body' => $body,
				'is_active' => $isActive,
			]
		);
	}
}
