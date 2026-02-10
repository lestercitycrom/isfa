<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Tenders;

use App\Livewire\Concerns\InteractsWithNotifications;
use App\Models\TenderKeywordDelivery;
use App\Models\TenderKeywordSubscription;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
final class Subscriptions extends Component
{
    use InteractsWithNotifications;

    public string $subscriptionKeyword = '';

    public bool $subscriptionActive = true;

    public function mount(): void
    {
        if (CompanyContext::isAdmin()) {
            $this->redirectRoute('admin.settings.notifications', navigate: true);

            return;
        }

        if (CompanyContext::companyId() === null) {
            abort(403);
        }
    }

    public function addKeywordSubscription(): void
    {
        $this->validate([
            'subscriptionKeyword' => ['required', 'string', 'max:255'],
            'subscriptionActive' => ['boolean'],
        ]);

        $keyword = trim(preg_replace('/\s+/u', ' ', $this->subscriptionKeyword) ?? '');
        if ($keyword === '') {
            $this->addError('subscriptionKeyword', __('common.required'));

            return;
        }

        $exists = $this->subscriptionsQuery()
            ->where('keyword', $keyword)
            ->exists();

        if ($exists) {
            $this->addError('subscriptionKeyword', __('common.keyword_subscription_exists'));

            return;
        }

        TenderKeywordSubscription::query()->create([
            'company_id' => CompanyContext::companyId(),
            'email' => (string) (Auth::user()?->email ?? ''),
            'keyword' => $keyword,
            'is_active' => $this->subscriptionActive,
        ]);

        $this->subscriptionKeyword = '';
        $this->dispatch('keyword-subscription-saved');
    }

    public function toggleKeywordSubscription(int $subscriptionId): void
    {
        $subscription = $this->subscriptionsQuery()
            ->whereKey($subscriptionId)
            ->first();

        if ($subscription === null) {
            return;
        }

        $subscription->update(['is_active' => ! $subscription->is_active]);
        $this->notifySuccess(__('common.saved'));
    }

    public function removeKeywordSubscription(int $subscriptionId): void
    {
        $this->subscriptionsQuery()
            ->whereKey($subscriptionId)
            ->delete();
        $this->notifySuccess(__('common.saved'));
    }

    public function render(): View
    {
        $subscriptionIds = $this->subscriptionsQuery()
            ->pluck('id');

        return view('livewire.admin.tenders.subscriptions', [
            'subscriptionEmail' => (string) (Auth::user()?->email ?? ''),
            'keywordSubscriptions' => $this->subscriptionsQuery()
                ->latest()
                ->get(),
            'keywordDeliveries' => TenderKeywordDelivery::query()
                ->with('subscription')
                ->whereIn('subscription_id', $subscriptionIds)
                ->latest()
                ->limit(100)
                ->get(),
        ]);
    }

    /**
     * @return Builder<TenderKeywordSubscription>
     */
    private function subscriptionsQuery(): Builder
    {
        $companyId = CompanyContext::companyId();
        $userEmail = (string) (Auth::user()?->email ?? '');

        return TenderKeywordSubscription::query()
            ->where('email', $userEmail)
            ->where('company_id', $companyId);
    }
}
