@extends('v2.layouts.app')

@section('title', 'Activity Logs')

@php
    // Two audiences share this template. The super admin view is a diagnostic
    // tool; the company view is a business record. $technical decides which.
    $technical = $technical ?? false;
    $columns = $scope === 'global' ? 8 : 7;
@endphp

@section('content')
<div class="space-y-5"
     x-data="activityLogView({
        detailUrl: '{{ route($detailRouteName, array_merge($detailRouteParams, ['log' => '__ID__'])) }}',
        filterUrl: @js($filterRoute),
        technical: @js($technical)
     })">

    <x-v2-breadcrumb :items="[['label' => 'Activity Logs']]" />

    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Activity Logs</h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                @if($technical)
                    Full platform trail with diagnostics — {{ $scope === 'global' ? 'every company' : ($company->name ?? 'this company') }}
                @else
                    Who did what in {{ $company->name ?? 'your company' }}
                @endif
            </p>
        </div>
        @if($technical)
        <span class="self-start sm:self-auto inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
            Super admin view
        </span>
        @endif
    </div>

    {{-- Counts for the current window. Each tile is a filter, so a number that
         looks wrong is one click from the rows behind it. --}}
    @isset($summary)
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @php
            $tiles = [
                ['label' => 'Total entries', 'value' => $summary['total'], 'query' => [], 'accent' => 'text-gray-900 dark:text-white', 'ring' => 'hover:border-gray-300 dark:hover:border-gray-600'],
                ['label' => 'Failed', 'value' => $summary['failed'], 'query' => ['status' => 'failed'], 'accent' => 'text-amber-600 dark:text-amber-400', 'ring' => 'hover:border-amber-300 dark:hover:border-amber-700'],
                ['label' => 'Permission denied', 'value' => $summary['denied'], 'query' => ['category' => 'authorization'], 'accent' => 'text-amber-600 dark:text-amber-400', 'ring' => 'hover:border-amber-300 dark:hover:border-amber-700'],
                ['label' => $technical ? 'Errors' : 'System issues', 'value' => $summary['errors'], 'query' => ['severity' => 'error'], 'accent' => 'text-red-600 dark:text-red-400', 'ring' => 'hover:border-red-300 dark:hover:border-red-700'],
            ];

            // Support access is not part of a company's own record.
            if ($technical && ($summary['impersonated'] ?? null) !== null) {
                $tiles[] = ['label' => 'Impersonated', 'value' => $summary['impersonated'], 'query' => ['impersonated' => 1], 'accent' => 'text-purple-600 dark:text-purple-400', 'ring' => 'hover:border-purple-300 dark:hover:border-purple-700'];
            }
        @endphp
        @foreach($tiles as $tile)
        <a href="{{ $filterRoute }}{{ $tile['query'] ? '?'.http_build_query($tile['query']) : '' }}"
           class="group bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 px-4 py-3 transition-colors {{ $tile['ring'] }}">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 truncate">{{ $tile['label'] }}</p>
            <p class="text-2xl font-semibold mt-0.5 {{ $tile['accent'] }}">{{ number_format($tile['value']) }}</p>
        </a>
        @endforeach
    </div>
    @endisset

    <div class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800">
        <form action="{{ $filterRoute }}" method="GET" class="p-3 space-y-3">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-2">
                <div class="lg:col-span-4 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}"
                           placeholder="Search person, action or IP..."
                           class="w-full pl-9 pr-3 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white placeholder-gray-500 focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>

                <div class="lg:col-span-2">
                    <x-filter-select name="category" :value="request('category')" :options="$technical ? \App\Models\ActivityLogs::CATEGORIES : \App\Models\ActivityLogs::companyCategories()" placeholder="All activity" class="w-full" />
                </div>

                @if($technical)
                <div class="lg:col-span-2">
                    <x-filter-select name="severity" :value="request('severity')" :options="\App\Models\ActivityLogs::SEVERITIES" placeholder="All severities" class="w-full" />
                </div>
                <div class="lg:col-span-1">
                    <x-filter-select name="method" :value="request('method')" :options="['GET' => 'GET', 'POST' => 'POST', 'PUT' => 'PUT', 'PATCH' => 'PATCH', 'DELETE' => 'DELETE']" placeholder="Method" class="w-full" />
                </div>
                @else
                <div class="lg:col-span-3">
                    <x-filter-select name="status" :value="request('status')" :options="['success' => 'Completed', 'failed' => 'Did not complete']" placeholder="Any outcome" class="w-full" />
                </div>
                @endif

                <div class="{{ $technical ? 'lg:col-span-3' : 'lg:col-span-3' }} grid grid-cols-2 gap-2">
                    <input type="date" name="from" value="{{ request('from') }}" aria-label="From date"
                           class="w-full px-2 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                    <input type="date" name="to" value="{{ request('to') }}" aria-label="To date"
                           class="w-full px-2 py-2 text-sm bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg text-gray-900 dark:text-white focus:border-primary-500 focus:ring-1 focus:ring-primary-500">
                </div>
            </div>

            @foreach(['session', 'ip', 'user', 'request'] as $sticky)
                @if(request($sticky))<input type="hidden" name="{{ $sticky }}" value="{{ request($sticky) }}">@endif
            @endforeach

            <div class="flex flex-wrap items-center gap-2">
                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">Search</button>
                <a href="{{ $filterRoute }}?noteworthy=1" class="px-3 py-2 text-sm border border-amber-300 dark:border-amber-700 text-amber-700 dark:text-amber-400 rounded-lg hover:bg-amber-50 dark:hover:bg-amber-900/20">
                    Needs review
                </a>
                @if(request()->hasAny(['search','method','status','category','severity','from','to','session','ip','impersonated','noteworthy','user','request']))
                <a href="{{ $filterRoute }}" class="px-3 py-2 text-sm text-gray-500 hover:text-gray-900 dark:hover:text-white">Clear all</a>
                @endif
            </div>
        </form>

        @php
            $chips = collect([
                'search' => request('search') ? 'Search: "'.request('search').'"' : null,
                'category' => request('category') ? 'Activity: '.(\App\Models\ActivityLogs::CATEGORIES[request('category')] ?? request('category')) : null,
                'severity' => request('severity') ? 'Severity: '.(\App\Models\ActivityLogs::SEVERITIES[request('severity')] ?? request('severity')) : null,
                'method' => request('method') ? 'Method: '.request('method') : null,
                'status' => request('status') ? 'Outcome: '.(request('status') === 'success' ? 'Completed' : 'Did not complete') : null,
                'from' => request('from') || request('to') ? (request('from') ?: 'any').' → '.(request('to') ?: 'now') : null,
                'ip' => request('ip') ? 'IP: '.request('ip') : null,
                'session' => request('session') ? 'One session' : null,
                'request' => request('request') ? 'One request' : null,
                'impersonated' => request('impersonated') ? 'Impersonated only' : null,
                'noteworthy' => request('noteworthy') ? 'Needs review' : null,
            ])->filter();
        @endphp
        @if($chips->isNotEmpty())
        <div class="px-3 pb-3 flex flex-wrap items-center gap-1.5 border-t border-gray-100 dark:border-gray-800 pt-3">
            @foreach($chips as $key => $label)
            <a href="{{ $filterRoute }}?{{ http_build_query(request()->except([$key, 'page'])) }}"
               class="group inline-flex items-center gap-1 px-2 py-1 text-xs rounded-md bg-gray-100 dark:bg-gray-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-700">
                {{ $label }}
                <span class="text-gray-400 group-hover:text-gray-700 dark:group-hover:text-gray-200">&times;</span>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <x-table-container>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Who</th>
                    @if($scope === 'global')
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden lg:table-cell">Company</th>
                    @endif
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">What happened</th>
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400">Outcome</th>
                    @if($technical)
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden xl:table-cell">Source</th>
                    @endif
                    <th class="px-3 py-2.5 text-left text-xs font-semibold text-gray-500 dark:text-gray-400 hidden md:table-cell">When</th>
                    <th class="w-20 px-3 py-2.5"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @forelse($activity_logs as $log)
                @php
                    $rowAccent = match(true) {
                        $log->severity === 'critical' => 'border-l-2 border-l-red-500',
                        $log->severity === 'error' => 'border-l-2 border-l-red-400',
                        $technical && $log->is_impersonated => 'border-l-2 border-l-purple-500',
                        $log->severity === 'warning' => 'border-l-2 border-l-amber-400',
                        default => 'border-l-2 border-l-transparent',
                    };
                @endphp
                <tr class="{{ $rowAccent }} hover:bg-gray-50 dark:hover:bg-gray-800/40 cursor-pointer" @click="open({{ $log->id }})">
                    <td class="px-3 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-white text-xs font-semibold flex-shrink-0 {{ $technical && $log->is_impersonated ? 'bg-purple-500' : 'bg-gray-400 dark:bg-gray-600' }}">
                                {{ strtoupper(substr($log->actor_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <span class="font-medium text-gray-900 dark:text-white block truncate max-w-[160px]">
                                    <x-search-highlight :text="$log->actor_name" :search="request('search')" />
                                </span>
                                @if($technical && $log->is_impersonated)
                                <span class="text-xs text-purple-600 dark:text-purple-400 truncate block max-w-[160px]">
                                    acting: {{ $log->impersonator_name }}
                                </span>
                                @elseif($log->actor_email)
                                <span class="text-xs text-gray-500 truncate block max-w-[160px]">{{ $log->actor_email }}</span>
                                @endif
                            </div>
                        </div>
                    </td>

                    @if($scope === 'global')
                    <td class="px-3 py-2.5 hidden lg:table-cell text-gray-700 dark:text-gray-300">
                        {{ $log->company?->name ?? '—' }}
                    </td>
                    @endif

                    <td class="px-3 py-2.5">
                        @php $shownDescription = $technical ? $log->description_with_actor : $log->description; @endphp
                        <p class="text-gray-900 dark:text-white truncate max-w-[340px]" title="{{ $shownDescription }}">
                            <x-search-highlight :text="$shownDescription" :search="request('search')" />
                        </p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            @php
                                $catStyle = [
                                    'auth' => 'bg-sky-50 dark:bg-sky-900/30 text-sky-700 dark:text-sky-400',
                                    'authorization' => 'bg-amber-50 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400',
                                    'impersonation' => 'bg-purple-50 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
                                    'error' => 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-400',
                                    'read' => 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400',
                                    'model' => 'bg-teal-50 dark:bg-teal-900/30 text-teal-700 dark:text-teal-400',
                                ][$log->category] ?? 'bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400';
                            @endphp
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-medium {{ $catStyle }}">{{ $log->category_label }}</span>
                            @if($technical && $log->method)
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-mono bg-gray-100 dark:bg-gray-800 text-gray-500 dark:text-gray-400">{{ $log->method }}</span>
                            @endif
                            @if($technical && $log->duration_ms !== null)
                            <span class="text-[10px] text-gray-400">{{ $log->duration_ms }}ms</span>
                            @endif
                        </div>
                    </td>

                    <td class="px-3 py-2.5">
                        @php
                            $sevStyle = [
                                'critical' => ['Critical', 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'],
                                'error' => ['Error', 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400'],
                                'warning' => ['Warning', 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'],
                                'notice' => ['Notice', 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400'],
                            ][$log->severity] ?? null;
                        @endphp
                        @if($sevStyle)
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium {{ $sevStyle[1] }}">{{ $technical ? $sevStyle[0] : 'Attention' }}</span>
                        @elseif($log->is_successful)
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">{{ $technical ? 'Success' : 'Completed' }}</span>
                        @else
                        <span class="px-2 py-0.5 text-xs rounded-full font-medium bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400">Failed</span>
                        @endif
                        @if($technical && $log->status_code)
                        <span class="block text-[10px] text-gray-400 mt-0.5 font-mono">HTTP {{ $log->status_code }}</span>
                        @endif
                    </td>

                    @if($technical)
                    <td class="px-3 py-2.5 hidden xl:table-cell">
                        <a href="{{ $filterRoute }}?ip={{ urlencode($log->ip_address ?? '') }}" @click.stop
                           class="font-mono text-xs text-gray-600 dark:text-gray-400 hover:text-primary-600 dark:hover:text-primary-400">
                            {{ $log->ip_address ?? '—' }}
                        </a>
                    </td>
                    @endif

                    <td class="px-3 py-2.5 hidden md:table-cell whitespace-nowrap">
                        {{-- Shown in the viewer's timezone; the row is stored in UTC.
                             diffForHumans is an elapsed duration, so it needs no conversion. --}}
                        <x-local-time :value="$log->created_at" format="short" class="text-gray-700 dark:text-gray-300" />
                        <span class="block text-xs text-gray-400">{{ $log->created_at->diffForHumans(null, true) }} ago</span>
                    </td>

                    <td class="px-3 py-2.5 text-right">
                        <span class="text-xs font-medium text-primary-600 dark:text-primary-400">Details →</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="{{ $columns }}" class="px-3 py-12 text-center">
                        <p class="text-gray-500 dark:text-gray-400">No activity matches these filters.</p>
                        @if($chips->isNotEmpty())
                        <a href="{{ $filterRoute }}" class="text-sm text-primary-600 dark:text-primary-400 hover:underline mt-1 inline-block">Clear filters</a>
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($activity_logs->hasPages())
        <div class="px-3 py-2 border-t border-gray-200 dark:border-gray-800">{{ $activity_logs->links() }}</div>
        @endif
    </x-table-container>

    @include('v2.activity-logs.partials.detail-drawer')
</div>

@push('scripts')
<script>
function activityLogView(config) {
    return {
        isOpen: false,
        loading: false,
        tab: 'summary',
        detail: {},

        open(id) {
            this.isOpen = true;
            this.loading = true;
            this.tab = 'summary';
            this.detail = {};
            document.body.style.overflow = 'hidden';

            fetch(config.detailUrl.replace('__ID__', id), { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : Promise.reject(r.status))
                .then(data => { this.detail = data; })
                .catch(() => { this.detail = { description: 'Could not load this entry.' }; })
                .finally(() => { this.loading = false; });
        },

        close() {
            this.isOpen = false;
            document.body.style.overflow = '';
        },

        get hasTechnical() {
            return config.technical && this.detail.technical && Object.keys(this.detail.technical).length > 0;
        },
        get hasChanges() {
            return this.detail.changes && Object.keys(this.detail.changes).length > 0;
        },
        get hasContext() {
            return this.detail.context && Object.keys(this.detail.context).length > 0;
        },

        summaryRows() {
            const d = this.detail;
            const rows = [
                { label: 'Person', value: d.actor },
                { label: 'Email', value: d.actor_email },
                { label: 'Performed by', value: d.impersonated ? d.impersonator : null },
                { label: 'Activity', value: d.category_label },
                { label: 'Outcome', value: d.successful === undefined ? null : (d.successful ? 'Completed' : 'Did not complete') },
                { label: 'Company', value: d.company },
                { label: 'Record', value: d.subject },
                { label: 'When', value: d.created_at },
            ];
            if (config.technical) {
                rows.push(
                    { label: 'Severity', value: d.severity },
                    { label: 'Method', value: d.method },
                    { label: 'URL', value: d.url },
                    { label: 'HTTP status', value: d.status_code },
                    { label: 'Duration', value: d.duration_ms == null ? null : d.duration_ms + ' ms' },
                    { label: 'IP address', value: d.ip_address },
                    { label: 'Device', value: d.user_agent },
                );
            }
            return rows.filter(r => r.value !== null && r.value !== undefined && r.value !== '');
        },

        linkTo(param) {
            const value = param === 'session' ? this.detail.session_id : this.detail.request_id;
            return config.filterUrl + '?' + param + '=' + encodeURIComponent(value || '');
        },

        isPair(change) {
            return change !== null && typeof change === 'object' && ('from' in change || 'to' in change);
        },

        format(value) {
            if (value === null || value === undefined || value === '') return 'empty';
            if (typeof value === 'object') return JSON.stringify(value);
            return String(value);
        },

        pretty(value) {
            return typeof value === 'string' ? value : JSON.stringify(value, null, 2);
        },

        copyTechnical() {
            const text = JSON.stringify({
                id: this.detail.id,
                action: this.detail.action,
                request_id: this.detail.request_id,
                url: this.detail.url,
                technical: this.detail.technical,
            }, null, 2);
            navigator.clipboard?.writeText(text);
        },
    };
}
</script>
@endpush
@endsection
