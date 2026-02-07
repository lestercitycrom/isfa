<?php

declare(strict_types=1);

use App\Livewire\Admin\Tenders\Subscriptions;
use App\Models\Company;
use App\Models\TenderKeywordSubscription;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    config()->set('activitylog.enabled', false);
});

it('creates keyword subscription for current user email', function (): void {
    $company = Company::query()->create([
        'name' => 'Acme LLC',
    ]);

    $user = User::factory()->create([
        'role' => User::ROLE_COMPANY,
        'company_id' => $company->id,
        'email' => 'user1@example.com',
    ]);

    $this->actingAs($user);

    Livewire::test(Subscriptions::class)
        ->set('subscriptionKeyword', 'printer laser')
        ->set('subscriptionActive', true)
        ->call('addKeywordSubscription')
        ->assertHasNoErrors();

    $subscription = TenderKeywordSubscription::query()->first();

    expect($subscription)->not->toBeNull();
    expect($subscription?->email)->toBe('user1@example.com');
    expect((int) $subscription?->company_id)->toBe((int) $company->id);
    expect($subscription?->keyword)->toBe('printer laser');
});

it('does not allow user to modify another users subscription', function (): void {
    $company = Company::query()->create([
        'name' => 'Beta LLC',
    ]);

    $owner = User::factory()->create([
        'role' => User::ROLE_COMPANY,
        'company_id' => $company->id,
        'email' => 'owner@example.com',
    ]);

    $other = User::factory()->create([
        'role' => User::ROLE_COMPANY,
        'company_id' => $company->id,
        'email' => 'other@example.com',
    ]);

    $subscription = TenderKeywordSubscription::query()->create([
        'company_id' => $company->id,
        'email' => $owner->email,
        'keyword' => 'office furniture',
        'is_active' => true,
    ]);

    $this->actingAs($other);

    Livewire::test(Subscriptions::class)
        ->call('removeKeywordSubscription', (int) $subscription->id)
        ->assertHasNoErrors();

    expect(TenderKeywordSubscription::query()->whereKey($subscription->id)->exists())->toBeTrue();
});

it('redirects admin from user subscriptions page to notification settings', function (): void {
    $admin = User::factory()->create([
        'role' => User::ROLE_ADMIN,
        'email' => 'admin@example.com',
    ]);

    $this->actingAs($admin)
        ->get(route('admin.tenders.subscriptions'))
        ->assertRedirect(route('admin.settings.notifications'));
});
