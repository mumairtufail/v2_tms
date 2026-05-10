@extends('v2.layouts.app')

@section('title', 'System Settings')

@section('content')
@php
    $settingsCards = [
        [
            'title'       => 'Branding & Logo',
            'description' => 'Upload light and dark mode logos used across the platform.',
            'route'       => route('admin.settings.branding'),
            'icon'        => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z',
            'tag'         => 'Branding',
        ],
    ];
@endphp

<div class="space-y-4" x-data="settingsHub()" x-init="init()">
    <x-v2-breadcrumb :items="[
        ['label' => 'Dashboard', 'url' => route('admin.dashboard')],
        ['label' => 'Settings']
    ]" />

    <div class="flex items-center justify-between gap-4">
        <x-page-header title="System Settings" description="Manage global platform preferences." />
        <div class="relative w-56 shrink-0">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-3.5 w-3.5 -translate-y-1/2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   x-model.debounce.200ms="query"
                   class="w-full rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900 pl-8 pr-7 py-1.5 text-sm text-gray-900 dark:text-gray-100 placeholder:text-gray-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                   placeholder="Search…">
            <button type="button" x-show="query" @click="query = ''" x-cloak class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <template x-for="card in filteredCards" :key="card.title">
            <a :href="card.route"
               class="group flex flex-col gap-3 rounded-xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-[#0B1120] p-4 shadow-sm hover:border-primary-200 dark:hover:border-primary-800 hover:shadow-md transition-all">
                <div class="flex items-center justify-between">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400 group-hover:bg-primary-50 dark:group-hover:bg-primary-900/20 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="card.icon"/>
                        </svg>
                    </div>
                    <span class="rounded bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 text-[10px] font-semibold uppercase tracking-wider text-gray-400 dark:text-gray-500" x-text="card.tag"></span>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors" x-html="highlight(card.title)"></p>
                    <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 leading-relaxed" x-text="card.description"></p>
                </div>
                <div class="mt-auto flex items-center gap-1 text-xs font-semibold text-gray-400 dark:text-gray-500 group-hover:text-primary-600 dark:group-hover:text-primary-400 transition-colors">
                    Open
                    <svg class="h-3.5 w-3.5 transition-transform group-hover:translate-x-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </div>
            </a>
        </template>
    </div>

    <template x-if="filteredCards.length === 0">
        <div class="rounded-xl border border-dashed border-gray-200 dark:border-gray-700 py-10 text-center">
            <p class="text-sm text-gray-500 dark:text-gray-400">No settings matched "<span x-text="query"></span>"</p>
        </div>
    </template>
</div>

@push('scripts')
<script>
function settingsHub() {
    return {
        query: '',
        cards: @json($settingsCards),
        filteredCards: [],

        init() {
            this.filteredCards = this.cards;
            this.$watch('query', () => {
                this.filteredCards = this.cards.filter(card => {
                    if (!this.query) return true;
                    return `${card.title} ${card.description} ${card.tag}`.toLowerCase().includes(this.query.toLowerCase());
                });
            });
        },

        highlight(text) {
            const q = this.query.trim();
            if (!q) return this.escapeHtml(text);
            const escaped = this.escapeHtml(text);
            const pattern = q.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
            return escaped.replace(new RegExp(`(${pattern})`, 'ig'), '<mark class="bg-primary-100 dark:bg-primary-900/40 text-primary-800 dark:text-primary-300 rounded px-0.5">$1</mark>');
        },

        escapeHtml(text) {
            return String(text).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
        },
    };
}
</script>
@endpush
@endsection
