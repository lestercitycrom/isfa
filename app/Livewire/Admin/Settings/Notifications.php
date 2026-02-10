<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Settings;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Mail\TenderReminderMail;
use App\Models\NotificationDelivery;
use App\Models\NotificationTemplate;
use App\Models\TenderKeywordDelivery;
use App\Models\TenderKeywordSubscription;
use App\Services\Notifications\TenderReminderTemplateRenderer;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Throwable;

#[Layout('layouts.admin')]
final class Notifications extends Component
{
    use InteractsWithNotifications;

    public string $tab = 'templates';

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

    public ?string $keywordTestResultStatus = null; // success|error

    public ?string $keywordTestResultMessage = null;

    public ?string $keywordTestResultDetails = null;

    public string $subscriptionEmail = '';

    public string $subscriptionKeyword = '';

    public bool $subscriptionActive = true;

    public function mount(): void
    {
        if (! CompanyContext::isAdmin()) {
            abort(403);
        }

        $this->loadTemplates();
        $this->testEmail = (string) (Auth::user()?->email ?? '');
    }

    public function setTab(string $tab): void
    {
        $allowed = ['templates', 'tests'];

        $this->tab = in_array($tab, $allowed, true) ? $tab : 'templates';
    }

    public function addKeywordSubscription(): void
    {
        $this->validate([
            'subscriptionEmail' => ['required', 'email', 'max:255'],
            'subscriptionKeyword' => ['required', 'string', 'max:255'],
            'subscriptionActive' => ['boolean'],
        ]);

        $companyId = CompanyContext::companyId();
        $keyword = trim(preg_replace('/\s+/u', ' ', $this->subscriptionKeyword) ?? '');
        if ($keyword === '') {
            $this->addError('subscriptionKeyword', __('common.required'));

            return;
        }

        $exists = TenderKeywordSubscription::query()
            ->where('email', $this->subscriptionEmail)
            ->where('keyword', $keyword)
            ->where(function ($query) use ($companyId): void {
                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->exists();

        if ($exists) {
            $this->addError('subscriptionKeyword', __('common.keyword_subscription_exists'));

            return;
        }

        TenderKeywordSubscription::query()->create([
            'company_id' => $companyId,
            'email' => $this->subscriptionEmail,
            'keyword' => $keyword,
            'is_active' => $this->subscriptionActive,
        ]);

        $this->subscriptionKeyword = '';
        $this->dispatch('keyword-subscription-saved');
    }

    public function toggleKeywordSubscription(int $subscriptionId): void
    {
        $companyId = CompanyContext::companyId();

        $subscription = TenderKeywordSubscription::query()
            ->whereKey($subscriptionId)
            ->where(function ($query) use ($companyId): void {
                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->first();

        if ($subscription === null) {
            return;
        }

        $subscription->update(['is_active' => ! $subscription->is_active]);
        $this->notifySuccess(__('common.saved'));
    }

    public function removeKeywordSubscription(int $subscriptionId): void
    {
        $companyId = CompanyContext::companyId();

        TenderKeywordSubscription::query()
            ->whereKey($subscriptionId)
            ->where(function ($query) use ($companyId): void {
                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->delete();
        $this->notifySuccess(__('common.saved'));
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
            $this->addError('testTemplateKey', __('common.template_not_found'));

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

    public function runKeywordCheckTest(): void
    {
        try {
            $exitCode = Artisan::call('tender:send-keyword-alerts');
            $output = trim((string) Artisan::output());

            $this->keywordTestResultStatus = $exitCode === 0 ? 'success' : 'error';
            $this->keywordTestResultMessage = $exitCode === 0
                ? __('common.keyword_test_completed')
                : __('common.keyword_test_failed');
            $this->keywordTestResultDetails = $output !== '' ? $output : null;

            if ($exitCode === 0) {
                $this->notifySuccess((string) $this->keywordTestResultMessage);
            } else {
                $this->notifyError((string) $this->keywordTestResultMessage);
            }
        } catch (Throwable $e) {
            Log::error('Keyword alerts test run failed', [
                'exception' => $e::class,
                'message' => $e->getMessage(),
            ]);

            $this->keywordTestResultStatus = 'error';
            $this->keywordTestResultMessage = __('common.keyword_test_failed');
            $this->keywordTestResultDetails = $e->getMessage();
            $this->notifyError((string) $this->keywordTestResultMessage);
        }
    }

    public function render(TenderReminderTemplateRenderer $renderer): View
    {
        $companyId = CompanyContext::companyId();

        $keywordSubscriptions = TenderKeywordSubscription::query()
            ->where(function ($query) use ($companyId): void {
                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->latest()
            ->get();

        $keywordDeliveries = TenderKeywordDelivery::query()
            ->with('subscription')
            ->where(function ($query) use ($companyId): void {
                if ($companyId === null) {
                    $query->whereNull('company_id');

                    return;
                }

                $query->where('company_id', $companyId);
            })
            ->latest()
            ->limit(200)
            ->get();

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
            'keywordSubscriptions' => $keywordSubscriptions,
            'keywordDeliveries' => $keywordDeliveries,
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
