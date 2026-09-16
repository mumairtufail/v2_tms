{{-- Credit standing, shown on every portal page once the account nears or reaches its limit --}}
@php
    $bannerCustomer = app()->bound('current.customer') ? app('current.customer') : auth('customer')->user()?->customer;
    $bannerCredit = $bannerCustomer ? app(\App\Services\CustomerCreditService::class)->usage($bannerCustomer) : null;
    $showBanner = $bannerCredit && $bannerCredit['has_limit'] && ($bannerCredit['over'] || ($bannerCredit['percent'] ?? 0) >= 80);
@endphp

@if($showBanner)
@if($bannerCredit['over'])
<div class="mb-6 rounded-xl border border-red-200 dark:border-red-900/60 bg-red-50 dark:bg-red-900/20 p-4 sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-red-800 dark:text-red-300">Credit limit reached</p>
            <p class="mt-1 text-sm leading-6 text-red-700 dark:text-red-400/90">
                Your account has used <strong class="font-semibold">${{ number_format($bannerCredit['used'], 2) }}</strong>
                of its <strong class="font-semibold">${{ number_format($bannerCredit['limit'], 2) }}</strong> credit limit,
                so new orders cannot be submitted at the moment. You can still prepare orders and save them as drafts —
                they will be ready to send as soon as credit is available.
            </p>

            <div class="mt-3 flex flex-wrap items-center gap-2">
                @if($company->phone)
                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $company->phone) }}"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-red-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-red-700 transition-colors">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Call {{ $company->phone }}
                </a>
                @endif
                <span class="text-xs text-red-700/80 dark:text-red-400/80">
                    Contact {{ $company->name }} to review your credit limit or settle an outstanding balance.
                </span>
            </div>
        </div>

        <div class="shrink-0 sm:text-right">
            <p class="text-[10px] font-bold uppercase tracking-wider text-red-700/70 dark:text-red-400/70">Credit used</p>
            <p class="text-lg font-bold tabular-nums text-red-800 dark:text-red-300">
                ${{ number_format($bannerCredit['used'], 0) }} <span class="opacity-60">/ ${{ number_format($bannerCredit['limit'], 0) }}</span>
            </p>
        </div>
    </div>
</div>
@else
<div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-900/60 bg-amber-50 dark:bg-amber-900/20 p-4 sm:p-5">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </span>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">Approaching your credit limit</p>
            <p class="mt-1 text-sm leading-6 text-amber-700 dark:text-amber-400/90">
                Your account has used <strong class="font-semibold">${{ number_format($bannerCredit['used'], 2) }}</strong>
                of its <strong class="font-semibold">${{ number_format($bannerCredit['limit'], 2) }}</strong> credit limit
                ({{ $bannerCredit['percent'] }}%). Once the limit is reached, new orders cannot be submitted until some of
                the balance is cleared.
            </p>
            @if($company->phone)
            <p class="mt-2 text-xs text-amber-700/80 dark:text-amber-400/80">
                Questions about your limit? Call {{ $company->name }} on {{ $company->phone }}.
            </p>
            @endif
        </div>

        <div class="shrink-0 sm:text-right">
            <p class="text-[10px] font-bold uppercase tracking-wider text-amber-700/70 dark:text-amber-400/70">Credit used</p>
            <p class="text-lg font-bold tabular-nums text-amber-800 dark:text-amber-300">
                ${{ number_format($bannerCredit['used'], 0) }} <span class="opacity-60">/ ${{ number_format($bannerCredit['limit'], 0) }}</span>
            </p>
        </div>
    </div>
</div>
@endif
@endif
