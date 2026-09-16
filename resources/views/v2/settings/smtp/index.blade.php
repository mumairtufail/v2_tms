@extends('v2.layouts.app')

@section('title', 'Email (SMTP)')

@section('content')
@php
    $activeSetting = $settings->firstWhere('is_active', true);
@endphp

<div class="space-y-5">
    <x-v2-breadcrumb :items="[
        ['label' => 'Settings', 'url' => $settingsIndexUrl],
        ['label' => 'Email (SMTP)']
    ]" />

    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <x-page-header title="Email (SMTP)" description="Connect the email account used to send password resets and other emails." />
        @if($settings->isNotEmpty())
            <a href="{{ $smtpUrl('create') }}" class="inline-flex shrink-0 items-center justify-center gap-2 px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add email account
            </a>
        @endif
    </div>

    @if($settings->isEmpty())
        {{-- Empty state --}}
        <div class="rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-[#0B1120] px-6 py-12 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400">
                <svg class="h-7 w-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h2 class="mt-4 text-lg font-bold text-gray-900 dark:text-white">Connect your email account</h2>
            <p class="mx-auto mt-1 max-w-md text-sm text-gray-500 dark:text-gray-400">
                Password resets and notifications will come from your company's own email address. It only takes a minute.
            </p>

            <ol class="mx-auto mt-8 grid max-w-3xl grid-cols-1 gap-3 text-left sm:grid-cols-3">
                @foreach([
                    ['Pick your provider', 'Gmail, Outlook, or any other email service.'],
                    ['Enter email & password', 'Server details are filled in for you.'],
                    ['Send a test', 'Confirm it works before emails go out.'],
                ] as $i => [$stepTitle, $stepText])
                    <li class="flex items-start gap-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 p-4">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-primary-600 text-xs font-bold text-white">{{ $i + 1 }}</span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $stepTitle }}</p>
                            <p class="mt-0.5 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $stepText }}</p>
                        </div>
                    </li>
                @endforeach
            </ol>

            <a href="{{ $smtpUrl('create') }}" class="mt-8 inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold rounded-lg shadow-sm shadow-primary-500/20 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add email account
            </a>
        </div>
    @else
        {{-- Current sending status --}}
        @if($activeSetting)
            <div class="flex items-start gap-3 rounded-xl border border-green-200 dark:border-green-900/50 bg-green-50 dark:bg-green-900/20 p-4">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-green-800 dark:text-green-300">Emails are sent from {{ $activeSetting->from_address }}</p>
                    <p class="mt-0.5 text-xs leading-5 text-green-700 dark:text-green-400/90">
                        Using "{{ $activeSetting->name }}" via {{ $activeSetting->provider_label }}. Password resets and other emails are queued and delivered through this account.
                    </p>
                </div>
            </div>
        @else
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-4">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-amber-800 dark:text-amber-300">No account is active</p>
                    <p class="mt-0.5 text-xs leading-5 text-amber-700 dark:text-amber-400/90">
                        Click "Set active" on one of your accounts below. Until then, emails use the system's default mail settings.
                    </p>
                </div>
            </div>
        @endif

        {{-- Accounts --}}
        <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
            @foreach($settings as $setting)
                <div class="flex flex-col rounded-xl border bg-white dark:bg-[#0B1120] shadow-sm transition-all {{ $setting->is_active ? 'border-primary-300 dark:border-primary-800 ring-1 ring-primary-100 dark:ring-primary-900/40' : 'border-gray-200 dark:border-gray-800 hover:border-gray-300 dark:hover:border-gray-700' }}">
                    <div class="flex items-start gap-3 p-4">
                        @include('v2.settings.smtp.partials.provider-badge', ['provider' => $setting->provider])
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2">
                                <a href="{{ $smtpUrl('show', $setting) }}" class="truncate text-sm font-semibold text-gray-900 dark:text-white hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                    {{ $setting->name }}
                                </a>
                                @if($setting->is_active)
                                    <span class="inline-flex shrink-0 items-center gap-1 rounded-full bg-primary-50 dark:bg-primary-900/30 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">
                                        <span class="h-1.5 w-1.5 rounded-full bg-primary-500"></span>
                                        Active
                                    </span>
                                @endif
                            </div>
                            <p class="mt-0.5 truncate text-xs text-gray-600 dark:text-gray-400">Sends from {{ $setting->from_address }}</p>
                            <p class="mt-0.5 truncate text-xs text-gray-400 dark:text-gray-500">{{ $setting->host }}:{{ $setting->port }} · {{ $setting->encryption_label }}</p>
                        </div>
                    </div>

                    <div class="mt-auto flex items-center justify-between gap-2 border-t border-gray-100 dark:border-gray-800 px-4 py-2.5">
                        @if(is_null($setting->last_test_passed))
                            <span class="text-xs text-gray-400 dark:text-gray-500">Not tested yet</span>
                        @elseif($setting->last_test_passed)
                            <span class="inline-flex items-center gap-1 text-xs text-green-600 dark:text-green-400">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Test passed {{ $setting->last_tested_at?->diffForHumans() }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs text-red-600 dark:text-red-400" title="{{ $setting->last_test_error }}">
                                <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Last test failed
                            </span>
                        @endif

                        <div class="flex items-center gap-0.5">
                            @unless($setting->is_active)
                                <form method="POST" action="{{ $smtpUrl('activate', $setting) }}" class="mr-1">
                                    @csrf
                                    <button type="submit" class="rounded-md px-2 py-1 text-xs font-semibold text-primary-600 dark:text-primary-400 hover:bg-primary-50 dark:hover:bg-primary-900/20 transition-colors">Set active</button>
                                </form>
                            @endunless
                            <a href="{{ $smtpUrl('show', $setting) }}" class="p-1.5 rounded-md text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800" title="View">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ $smtpUrl('edit', $setting) }}" class="p-1.5 rounded-md text-gray-400 hover:text-primary-600 dark:hover:text-primary-400 hover:bg-gray-100 dark:hover:bg-gray-800" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <button type="button" x-data @click="$dispatch('open-modal', 'delete-smtp-{{ $setting->id }}')" class="p-1.5 rounded-md text-gray-400 hover:text-red-600 dark:hover:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-800" title="Delete">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <p class="flex items-center gap-1.5 text-xs text-gray-500 dark:text-gray-400">
            <svg class="h-3.5 w-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            You can save several accounts (for example a backup), but only one sends emails at a time.
        </p>
    @endif
</div>

@foreach($settings as $setting)
    @include('v2.settings.smtp.partials.delete-modal', ['setting' => $setting])
@endforeach
@endsection
