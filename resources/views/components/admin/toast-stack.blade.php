@props([
    'initialToast' => null,
    'eventToMessage' => [],
])

<div
    x-data="adminToastStack(@js($initialToast))"
    x-on:notify.window="push($event.detail)"
    x-on:toast.window="push($event.detail)"
    x-on:comment-saved.window="push({ type: 'success', message: @js($eventToMessage['comment-saved'] ?? '') })"
    x-on:attributes-saved.window="push({ type: 'success', message: @js($eventToMessage['attributes-saved'] ?? '') })"
    x-on:profile-updated.window="push({ type: 'success', message: @js($eventToMessage['profile-updated'] ?? '') })"
    x-on:password-updated.window="push({ type: 'success', message: @js($eventToMessage['password-updated'] ?? '') })"
    x-on:company-updated.window="push({ type: 'success', message: @js($eventToMessage['company-updated'] ?? '') })"
    x-on:keyword-subscription-saved.window="push({ type: 'success', message: @js($eventToMessage['keyword-subscription-saved'] ?? '') })"
    x-on:templates-saved.window="push({ type: 'success', message: @js($eventToMessage['templates-saved'] ?? '') })"
    x-on:test-email-sent.window="push({ type: 'success', message: @js($eventToMessage['test-email-sent'] ?? '') })"
    class="pointer-events-none fixed right-4 top-4 z-[100] flex w-full max-w-[26rem] flex-col gap-2"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.show"
            x-transition:enter="transform transition ease-out duration-220"
            x-transition:enter-start="opacity-0 translate-y-2 scale-[.98]"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-180"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="pointer-events-auto relative overflow-hidden rounded-2xl border border-slate-700/80 bg-[linear-gradient(180deg,#091734_0%,#0b1b3f_100%)] px-4 py-3 text-slate-100 shadow-[0_14px_36px_-22px_rgba(0,0,0,0.9)]"
        >
            <div class="flex items-center gap-3">
                <span
                    class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full"
                    :class="badgeClass(toast.type)"
                    x-html="iconSvg(toast.type)"
                ></span>

                <p class="min-w-0 flex-1 text-sm leading-5 text-slate-100/95" x-text="toast.message"></p>

                <button
                    type="button"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-slate-300/80 transition hover:bg-slate-100/10 hover:text-slate-100"
                    x-on:click="close(toast.id)"
                    aria-label="{{ __('Close notification') }}"
                >
                    <svg viewBox="0 0 20 20" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round">
                        <path d="M5 5l10 10M15 5 5 15" />
                    </svg>
                </button>
            </div>
        </div>
    </template>
</div>

<script>
    function adminToastStack(initialToast) {
        return {
            toasts: [],
            maxVisible: 5,
            defaultTimeoutByType: {
                success: 3200,
                info: 3200,
                warning: 4200,
                error: 5200,
            },
            typeMap: {
                created: 'success',
                updated: 'success',
                deleted: 'error',
            },
            init() {
                if (initialToast && initialToast.message) {
                    this.push(initialToast);
                }
            },
            normalizeType(type) {
                const normalized = String(type ?? 'info').toLowerCase();
                return this.typeMap[normalized] ?? normalized;
            },
            badgeClass(type) {
                const t = this.normalizeType(type);
                if (t === 'success') return 'bg-emerald-300/12 text-emerald-200';
                if (t === 'error') return 'bg-rose-300/12 text-rose-200';
                if (t === 'warning') return 'bg-amber-300/12 text-amber-200';
                return 'bg-sky-300/12 text-sky-200';
            },
            iconSvg(type) {
                const t = this.normalizeType(type);
                if (t === 'success') {
                    return '<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 10.5 8 14l7.5-8"/></svg>';
                }
                if (t === 'error') {
                    return '<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 6l8 8M14 6l-8 8"/></svg>';
                }
                if (t === 'warning') {
                    return '<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 3 3.5 15h13L10 3Z"/><path d="M10 7.6v3.8M10 14h.01"/></svg>';
                }
                return '<svg viewBox="0 0 20 20" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="10" cy="10" r="7"/><path d="M10 8v5M10 6h.01"/></svg>';
            },
            push(payload) {
                const message = payload?.message ?? '';
                if (!message) return;

                const type = this.normalizeType(payload?.type);
                const timeout = Number(payload?.timeout ?? this.defaultTimeoutByType[type] ?? 3200);
                const toast = {
                    id: (window.crypto?.randomUUID?.() ?? String(Date.now() + Math.random())),
                    type,
                    message,
                    show: true,
                    timeout,
                };

                this.toasts.unshift(toast);
                if (this.toasts.length > this.maxVisible) {
                    this.toasts = this.toasts.slice(0, this.maxVisible);
                }
                window.setTimeout(() => this.close(toast.id), toast.timeout);
            },
            close(id) {
                const toast = this.toasts.find((item) => item.id === id);
                if (!toast) return;
                toast.show = false;
                window.setTimeout(() => {
                    this.toasts = this.toasts.filter((item) => item.id !== id);
                }, 220);
            },
        };
    }
</script>
