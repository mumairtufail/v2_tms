@extends('v2.layouts.app')

@section('title', 'System Branding')

@section('content')
@php
    $logoCards = [
        [
            'key'   => 'logo_light',
            'label' => 'Light Logo',
            'hint'  => 'Used on light backgrounds and documents.',
            'value' => $settings->logo_light,
        ],
        [
            'key'   => 'logo_dark',
            'label' => 'Dark Logo',
            'hint'  => 'Used on dark surfaces and dark mode.',
            'value' => $settings->logo_dark,
        ],
    ];
@endphp

<div class="space-y-6" x-data="brandingSettings()">
    <x-v2-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Settings', 'url' => route('admin.settings.index')],
        ['label' => 'Branding & Logo']
    ]" />

    <div class="flex flex-col gap-2">
        <x-page-header title="Branding & Logo" description="Upload light and dark mode logos used across the platform." />
        <p class="text-xs text-gray-500 dark:text-gray-400 max-w-3xl">
            These assets appear in the admin panel and on system-level pages. Company-specific logos always take precedence within their own workspace.
        </p>
    </div>

    @if(session('success'))
        <div class="rounded-lg border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-900/20 px-4 py-3 text-sm font-medium text-green-700 dark:text-green-400">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.branding.update') }}" enctype="multipart/form-data" @submit="submitting = true"
          class="space-y-6 rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#0B1120] p-5 shadow-sm">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($logoCards as $card)
                <div class="rounded-2xl border border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 p-4 space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ $card['label'] }}</h3>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">{{ $card['hint'] }}</p>
                        </div>
                        <span class="rounded-full bg-primary-50 dark:bg-primary-900/20 px-2.5 py-1 text-[10px] font-bold uppercase tracking-wider text-primary-700 dark:text-primary-300">
                            Upload
                        </span>
                    </div>

                    <div class="flex items-center justify-center rounded-2xl border border-dashed border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-950 min-h-[180px] p-4">
                        <template x-if="previews.{{ $card['key'] }}">
                            <img :src="previews.{{ $card['key'] }}" alt="{{ $card['label'] }} preview" class="max-h-32 max-w-full object-contain">
                        </template>
                        <template x-if="!previews.{{ $card['key'] }} && existing.{{ $card['key'] }}">
                            <img src="{{ asset('storage/' . $card['value']) }}" alt="{{ $card['label'] }} current" class="max-h-32 max-w-full object-contain">
                        </template>
                        <template x-if="!previews.{{ $card['key'] }} && !existing.{{ $card['key'] }}">
                            <div class="text-center">
                                <svg class="mx-auto h-10 w-10 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="mt-2 text-xs text-gray-400 dark:text-gray-500">No file selected</p>
                            </div>
                        </template>
                    </div>

                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-wider text-gray-500 dark:text-gray-400">Choose file</label>
                        <input type="file"
                               name="{{ $card['key'] }}"
                               accept="image/*"
                               @change="previewFile($event, '{{ $card['key'] }}')"
                               class="mt-1 block w-full text-sm text-gray-600 dark:text-gray-300 file:mr-4 file:rounded-xl file:border-0 file:bg-primary-600 file:px-4 file:py-2 file:text-sm file:font-bold file:text-white hover:file:bg-primary-700 dark:file:bg-primary-500 dark:hover:file:bg-primary-400">
                    </div>
                </div>
            @endforeach
        </div>

        <div class="flex items-center justify-between border-t border-gray-200 dark:border-gray-800 pt-4">
            <p class="text-xs text-gray-500 dark:text-gray-400">PNG, JPG, or SVG. Max 3MB per file.</p>
            <button type="submit"
                    class="inline-flex items-center gap-2 rounded-xl bg-primary-600 px-4 py-2 text-sm font-bold text-white shadow-sm shadow-primary-500/20 transition-all hover:bg-primary-700 disabled:cursor-not-allowed disabled:opacity-60"
                    :disabled="submitting">
                <span x-show="submitting"><x-loader size="xs" class="text-white" /></span>
                <span x-show="!submitting">Save Branding</span>
            </button>
        </div>
    </form>
</div>

@push('scripts')
<script>
function brandingSettings() {
    return {
        submitting: false,
        existing: {
            logo_light: @json((bool) $settings->logo_light),
            logo_dark:  @json((bool) $settings->logo_dark),
        },
        previews: {
            logo_light: null,
            logo_dark:  null,
        },
        previewFile(event, key) {
            const file = event.target.files?.[0];
            if (!file) return;
            this.previews[key] = URL.createObjectURL(file);
        },
    };
}
</script>
@endpush
@endsection
