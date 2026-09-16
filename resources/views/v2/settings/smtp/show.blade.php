@extends('v2.layouts.app')

@section('title', $setting->name)

@section('content')
@php
    $meta = $setting->providerMeta();
    $cardClass = 'rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#0B1120] shadow-sm';
    $details = [
        ['Provider', $meta['label'] . ' — ' . $meta['subtitle']],
        ['Sends as', ($setting->from_name ? $setting->from_name . ' ' : '') . '<' . $setting->from_address . '>'],
        ['Username', $setting->username],
        ['Password', '••••••••••••'],
        ['Server address', $setting->host],
        ['Port & encryption', $setting->port . ' · ' . $setting->encryption_label],
        ['Added', $setting->created_at?->format('M j, Y')],
        ['Last updated', $setting->updated_at?->diffForHumans()],
    ];
@endphp

<div class="space-y-5">
    <x-v2-breadcrumb :items="[
        ['label' => 'Settings', 'url' => $settingsIndexUrl],
        ['label' => 'Email (SMTP)', 'url' => $smtpUrl('index')],
        ['label' => $setting->name]
    ]" />

    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-center gap-3">
            <a href="{{ $smtpUrl('index') }}" class="p-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 transition-colors rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            @include('v2.settings.smtp.partials.provider-badge', ['provider' => $setting->provider, 'size' => 'lg'])
            <div class="min-w-0">
                <div class="flex items-center gap-2">
                    <h1 class="truncate text-xl font-bold text-gray-900 dark:text-white">{{ $setting->name }}</h1>
                    @if($setting->is_active)
                        <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">
                            <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                            Active
                        </span>
                    @else
                        <span class="inline-flex shrink-0 rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Inactive</span>
                    @endif
                </div>
                <p class="truncate text-sm text-gray-500 dark:text-gray-400">{{ $setting->from_address }}</p>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ $smtpUrl('edit', $setting) }}" class="inline-flex items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 px-3.5 py-2 text-sm font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Edit
            </a>
            <button type="button" x-data @click="$dispatch('open-modal', 'delete-smtp-{{ $setting->id }}')" class="inline-flex items-center gap-2 rounded-lg border border-red-200 dark:border-red-900/60 bg-white dark:bg-gray-900 px-3.5 py-2 text-sm font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                Delete
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <div class="space-y-5 lg:col-span-2">
            {{-- Status --}}
            @if($setting->is_active)
                <div class="flex flex-col gap-3 rounded-xl border border-green-200 dark:border-green-900/50 bg-green-50 dark:bg-green-900/20 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-green-800 dark:text-green-300">This account is sending your emails</p>
                            <p class="mt-0.5 text-xs leading-5 text-green-700 dark:text-green-400/90">Password resets and other emails go out from {{ $setting->from_address }}.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ $smtpUrl('deactivate', $setting) }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg border border-green-300 dark:border-green-800 px-3 py-1.5 text-xs font-semibold text-green-800 dark:text-green-300 hover:bg-green-100 dark:hover:bg-green-900/40 transition-colors">Deactivate</button>
                    </form>
                </div>
            @else
                <div class="flex flex-col gap-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">This account is not in use</p>
                            <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">Set it as active to send emails through it. The account currently in use will be switched off.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ $smtpUrl('activate', $setting) }}" class="shrink-0">
                        @csrf
                        <button type="submit" class="rounded-lg bg-primary-600 px-3.5 py-2 text-xs font-semibold text-white hover:bg-primary-700 transition-colors">Set as active</button>
                    </form>
                </div>
            @endif

            {{-- Details --}}
            <div class="{{ $cardClass }}">
                <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-4">
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Account details</h3>
                </div>
                <dl class="grid grid-cols-1 sm:grid-cols-2">
                    @foreach($details as [$label, $value])
                        <div class="border-b border-gray-100 dark:border-gray-800 px-5 py-3.5 sm:[&:nth-last-child(-n+2)]:border-b-0 last:border-b-0">
                            <dt class="text-[11px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500">{{ $label }}</dt>
                            <dd class="mt-1 break-words text-sm text-gray-900 dark:text-gray-100">
                                {{ $value ?: '—' }}
                                @if($label === 'Password')
                                    <span class="ml-1 text-xs text-gray-400 dark:text-gray-500">(encrypted)</span>
                                @endif
                            </dd>
                        </div>
                    @endforeach
                </dl>
            </div>
        </div>

        {{-- Test --}}
        <aside>
            <div class="{{ $cardClass }} p-5">
                <div class="flex items-center gap-2">
                    <svg class="h-4 w-4 text-primary-600 dark:text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Send a test email</h3>
                </div>
                <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">Check this account can sign in and deliver email.</p>

                <form method="POST" action="{{ $smtpUrl('test', $setting) }}" x-data="{ sending: false }" @submit="sending = true" class="mt-4">
                    @csrf
                    <label for="test_to" class="block mb-1.5 text-xs font-medium text-gray-700 dark:text-gray-300">Send test to</label>
                    <input id="test_to" name="test_to" type="email" required value="{{ old('test_to', auth()->user()->email) }}"
                           class="w-full px-3.5 py-2.5 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <x-input-error :messages="$errors->get('test_to')" class="mt-1" />
                    <button type="submit" :disabled="sending"
                            class="mt-3 inline-flex w-full items-center justify-center gap-2 rounded-lg bg-primary-600 px-4 py-2 text-sm font-semibold text-white hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60 transition-colors">
                        <svg x-show="sending" x-cloak class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="sending ? 'Sending…' : 'Send test email'">Send test email</span>
                    </button>
                </form>

                @if(is_null($setting->last_test_passed))
                    <p class="mt-4 flex items-center gap-1.5 text-xs text-amber-600 dark:text-amber-400">
                        <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Not tested yet
                    </p>
                @else
                    <div class="mt-4 rounded-lg border p-3 text-xs leading-5 {{ $setting->last_test_passed
                        ? 'border-green-200 bg-green-50 text-green-800 dark:border-green-900/50 dark:bg-green-900/20 dark:text-green-300'
                        : 'border-red-200 bg-red-50 text-red-800 dark:border-red-900/50 dark:bg-red-900/20 dark:text-red-300' }}">
                        <p class="font-semibold">
                            {{ $setting->last_test_passed ? 'Last test passed' : 'Last test failed' }}
                            <span class="font-normal opacity-80">· {{ $setting->last_tested_at?->diffForHumans() }}</span>
                        </p>
                        @if(! $setting->last_test_passed && $setting->last_test_error)
                            <p class="mt-1">{{ $setting->last_test_error }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>

@include('v2.settings.smtp.partials.delete-modal', ['setting' => $setting])
@endsection
