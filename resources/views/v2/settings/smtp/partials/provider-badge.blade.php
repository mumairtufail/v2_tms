@php
    $badgeColors = [
        'gmail'    => 'bg-red-50 text-red-600 dark:bg-red-900/30 dark:text-red-400',
        'outlook'  => 'bg-blue-50 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400',
        'yahoo'    => 'bg-violet-50 text-violet-600 dark:bg-violet-900/30 dark:text-violet-400',
        'zoho'     => 'bg-amber-50 text-amber-600 dark:bg-amber-900/30 dark:text-amber-400',
        'sendgrid' => 'bg-sky-50 text-sky-600 dark:bg-sky-900/30 dark:text-sky-400',
        'mailgun'  => 'bg-rose-50 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400',
        'ses'      => 'bg-orange-50 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400',
        'custom'   => 'bg-gray-100 text-gray-500 dark:bg-gray-800 dark:text-gray-400',
    ];
    $badgeInitials = ['gmail' => 'G', 'outlook' => 'O', 'yahoo' => 'Y', 'zoho' => 'Z', 'sendgrid' => 'SG', 'mailgun' => 'MG', 'ses' => 'SES'];
    $badgeKey = array_key_exists($provider ?? '', $badgeColors) ? $provider : 'custom';
    $badgeSize = ($size ?? 'md') === 'lg' ? 'h-11 w-11 rounded-xl text-sm' : 'h-9 w-9 rounded-lg text-xs';
@endphp

<div class="flex {{ $badgeSize }} shrink-0 items-center justify-center font-bold {{ $badgeColors[$badgeKey] }}">
    @if(isset($badgeInitials[$badgeKey]))
        <span class="{{ strlen($badgeInitials[$badgeKey]) > 2 ? 'text-[10px]' : '' }}">{{ $badgeInitials[$badgeKey] }}</span>
    @else
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01" />
        </svg>
    @endif
</div>
