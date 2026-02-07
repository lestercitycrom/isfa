<div class="space-y-6">
    <x-admin.page-header
        :title="__('common.my_keyword_subscriptions')"
        :subtitle="__('common.my_keyword_subscriptions_subtitle')"
    />

    <x-admin.card :title="__('common.keyword_subscriptions')">
        <form wire:submit.prevent="addKeywordSubscription" class="space-y-4">
            <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-700">
                <div class="font-semibold text-slate-900">{{ __('common.email') }}</div>
                <div class="mt-1">{{ $subscriptionEmail }}</div>
                <div class="mt-2 text-xs text-slate-500">{{ __('common.keyword_email_bound_hint') }}</div>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                <x-admin.input
                    :label="__('common.keywords')"
                    type="text"
                    wire:model="subscriptionKeyword"
                    :error="$errors->first('subscriptionKeyword')"
                />
                <div class="flex items-end gap-3">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-700">
                        <input type="checkbox" wire:model="subscriptionActive" class="rounded border-slate-300 text-slate-900 focus:ring-slate-300" />
                        <span>{{ __('common.active') }}</span>
                    </label>
                    <x-admin.button variant="primary" type="submit">
                        {{ __('common.add') }}
                    </x-admin.button>
                </div>
            </div>

            <div class="text-xs text-slate-500">{{ __('common.keywords_hint') }}</div>
            <x-action-message class="text-sm text-slate-600" on="keyword-subscription-saved">
                {{ __('common.saved') }}
            </x-action-message>
        </form>
    </x-admin.card>

    <x-admin.card :title="__('common.keyword_subscriptions')">
        <x-admin.table :zebra="true">
            <x-slot name="head">
                <tr>
                    <x-admin.th>{{ __('common.keywords') }}</x-admin.th>
                    <x-admin.th>{{ __('common.status') }}</x-admin.th>
                    <x-admin.th>{{ __('common.time') }}</x-admin.th>
                    <x-admin.th align="right">{{ __('common.actions') }}</x-admin.th>
                </tr>
            </x-slot>
            @forelse($keywordSubscriptions as $subscription)
                <tr>
                    <x-admin.td>{{ $subscription->keyword }}</x-admin.td>
                    <x-admin.td>
                        <x-admin.badge variant="{{ $subscription->is_active ? 'green' : 'slate' }}">
                            {{ $subscription->is_active ? __('common.active') : __('common.inactive') }}
                        </x-admin.badge>
                    </x-admin.td>
                    <x-admin.td>{{ $subscription->last_checked_at?->format('Y-m-d H:i') ?? '-' }}</x-admin.td>
                    <x-admin.td align="right">
                        <div class="inline-flex items-center gap-2">
                            <x-admin.button variant="secondary" size="sm" wire:click="toggleKeywordSubscription({{ $subscription->id }})">
                                {{ $subscription->is_active ? __('common.disable') : __('common.enable') }}
                            </x-admin.button>
                            <x-admin.icon-button
                                icon="trash"
                                :title="__('common.delete')"
                                variant="danger"
                                wire:click="removeKeywordSubscription({{ $subscription->id }})"
                            />
                        </div>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <x-admin.td colspan="4" class="text-center py-8 text-slate-500">
                        {{ __('common.no_records') }}
                    </x-admin.td>
                </tr>
            @endforelse
        </x-admin.table>
    </x-admin.card>

    <x-admin.card :title="__('common.keyword_send_log')">
        <x-admin.table :zebra="true">
            <x-slot name="head">
                <tr>
                    <x-admin.th>{{ __('common.time') }}</x-admin.th>
                    <x-admin.th>{{ __('common.keywords') }}</x-admin.th>
                    <x-admin.th>{{ __('common.tender') }}</x-admin.th>
                    <x-admin.th>{{ __('common.status') }}</x-admin.th>
                </tr>
            </x-slot>
            @forelse($keywordDeliveries as $delivery)
                <tr>
                    <x-admin.td>{{ $delivery->created_at?->format('Y-m-d H:i') ?? '-' }}</x-admin.td>
                    <x-admin.td>{{ $delivery->subscription?->keyword ?? '-' }}</x-admin.td>
                    <x-admin.td>
                        @if($delivery->event_url)
                            <a href="{{ $delivery->event_url }}" target="_blank" class="underline hover:text-slate-700">
                                {{ $delivery->event_title ?: $delivery->event_id }}
                            </a>
                        @else
                            {{ $delivery->event_title ?: $delivery->event_id }}
                        @endif
                    </x-admin.td>
                    <x-admin.td>
                        <x-admin.badge variant="{{ $delivery->status === 'sent' ? 'green' : ($delivery->status === 'failed' ? 'red' : 'slate') }}">
                            {{ $delivery->status }}
                        </x-admin.badge>
                    </x-admin.td>
                </tr>
            @empty
                <tr>
                    <x-admin.td colspan="4" class="text-center py-8 text-slate-500">
                        {{ __('common.no_records') }}
                    </x-admin.td>
                </tr>
            @endforelse
        </x-admin.table>
    </x-admin.card>
</div>
