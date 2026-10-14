{{--
    Detail drawer for one activity entry.

    The Summary and Changes tabs are what a company user needs. The Diagnostics
    tab only renders when $technical is true, and the payload behind it is only
    sent by the controller for a super admin - the gate is on the server, not
    just here.
--}}
<div x-show="isOpen" x-cloak class="fixed inset-0 z-50 flex justify-end" @keydown.escape.window="close()">
    <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px]" @click="close()"></div>

    <div class="relative w-full sm:max-w-2xl h-full flex flex-col bg-white dark:bg-gray-900 shadow-2xl border-l border-gray-200 dark:border-gray-800"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="translate-x-full"
         x-transition:enter-end="translate-x-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="translate-x-0"
         x-transition:leave-end="translate-x-full">

        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-800 flex items-start justify-between gap-3">
            <div class="min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <template x-if="detail.severity && ['critical','error'].includes(detail.severity)">
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold uppercase tracking-wide bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400" x-text="detail.severity"></span>
                    </template>
                    <template x-if="detail.severity === 'warning'">
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold uppercase tracking-wide bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400">warning</span>
                    </template>
                    <template x-if="detail.impersonated">
                        <span class="px-2 py-0.5 text-[10px] rounded-full font-semibold uppercase tracking-wide bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400">impersonated</span>
                    </template>
                </div>
                <h3 class="font-semibold text-gray-900 dark:text-white leading-snug" x-text="detail.description || 'Activity detail'"></h3>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{-- Viewer's timezone, falling back to the server's UTC string --}}
                    <span x-text="detail.created_at_iso ? window.localTime(detail.created_at_iso, 'datetime-seconds') : detail.created_at"></span>
                    <template x-if="detail.actor"><span> · <span x-text="detail.actor"></span></span></template>
                </p>
            </div>
            <button type="button" @click="close()" aria-label="Close"
                    class="flex-shrink-0 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-700 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-gray-800 text-xl leading-none">&times;</button>
        </div>

        <div class="px-5 pt-3 border-b border-gray-200 dark:border-gray-800 flex gap-1">
            @php
                $tabs = [['summary', 'Summary'], ['changes', 'Changes']];
                if ($technical) { $tabs[] = ['technical', 'Diagnostics']; }
            @endphp
            @foreach($tabs as [$key, $label])
            <button type="button" @click="tab = '{{ $key }}'"
                    :class="tab === '{{ $key }}'
                        ? 'border-primary-600 text-primary-600 dark:text-primary-400'
                        : 'border-transparent text-gray-500 hover:text-gray-800 dark:hover:text-gray-200'"
                    class="px-3 py-2 text-sm font-medium border-b-2 -mb-px transition-colors">
                {{ $label }}
                @if($key === 'changes')<span x-show="hasChanges" class="ml-1 text-xs text-gray-400" x-text="'(' + Object.keys(detail.changes || {}).length + ')'"></span>@endif
            </button>
            @endforeach
        </div>

        <div class="flex-1 overflow-y-auto">
            <div x-show="loading" class="p-5 text-sm text-gray-500">Loading...</div>

            <div x-show="!loading" class="p-5 space-y-5">

                <template x-if="detail.impersonated && tab === 'summary'">
                    <div class="rounded-lg bg-purple-50 dark:bg-purple-900/20 border border-purple-200 dark:border-purple-800 px-3 py-2.5 text-sm text-purple-900 dark:text-purple-300">
                        Performed by <strong x-text="detail.impersonator"></strong> while signed in as <strong x-text="detail.actor"></strong>.
                    </div>
                </template>

                {{-- Summary --}}
                <div x-show="tab === 'summary'" class="space-y-4">
                    <dl class="divide-y divide-gray-100 dark:divide-gray-800">
                        <template x-for="row in summaryRows()" :key="row.label">
                            <div class="py-2 grid grid-cols-3 gap-3 text-sm">
                                <dt class="text-gray-500 dark:text-gray-400" x-text="row.label"></dt>
                                <dd class="col-span-2 text-gray-900 dark:text-white break-all" x-text="row.value"></dd>
                            </div>
                        </template>
                    </dl>

                    <div class="flex flex-wrap gap-2">
                        <template x-if="detail.session_id">
                            <a :href="linkTo('session')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-primary-400 hover:text-primary-600">
                                All activity in this session
                            </a>
                        </template>
                        @if($technical)
                        <template x-if="detail.request_id">
                            <a :href="linkTo('request')" class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-primary-400 hover:text-primary-600">
                                Everything from this request
                            </a>
                        </template>
                        @endif
                    </div>

                    <template x-if="hasContext">
                        <div>
                            <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Details</h4>
                            <dl class="rounded-lg border border-gray-200 dark:border-gray-800 divide-y divide-gray-100 dark:divide-gray-800">
                                <template x-for="(value, key) in detail.context" :key="key">
                                    <div class="px-3 py-2 grid grid-cols-3 gap-3 text-sm">
                                        <dt class="text-gray-500 dark:text-gray-400 break-all" x-text="key"></dt>
                                        <dd class="col-span-2 text-gray-900 dark:text-white break-all" x-text="format(value)"></dd>
                                    </div>
                                </template>
                            </dl>
                        </div>
                    </template>
                </div>

                {{-- Changes --}}
                <div x-show="tab === 'changes'">
                    <template x-if="!hasChanges">
                        <p class="text-sm text-gray-500">This entry did not change any stored values.</p>
                    </template>
                    <template x-if="hasChanges">
                        <div class="rounded-lg border border-gray-200 dark:border-gray-800 divide-y divide-gray-100 dark:divide-gray-800">
                            <template x-for="(change, field) in detail.changes" :key="field">
                                <div class="px-3 py-2.5">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white mb-1" x-text="field"></p>
                                    {{-- Redacted fields are stored as a bare value, not a before/after pair. --}}
                                    <template x-if="isPair(change)">
                                        <div class="flex items-start gap-2 text-xs">
                                            <span class="flex-1 px-2 py-1 rounded bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-400 break-all" x-text="format(change.from)"></span>
                                            <span class="text-gray-400 pt-1">→</span>
                                            <span class="flex-1 px-2 py-1 rounded bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-400 break-all" x-text="format(change.to)"></span>
                                        </div>
                                    </template>
                                    <template x-if="!isPair(change)">
                                        <p class="text-xs text-gray-500">changed <span x-text="format(change)"></span></p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>

                @if($technical)
                {{-- Diagnostics: super admin only --}}
                <div x-show="tab === 'technical'" class="space-y-4">
                    <template x-if="!hasTechnical">
                        <p class="text-sm text-gray-500">No diagnostic data was captured for this entry. Entries only carry a stack trace when something failed.</p>
                    </template>

                    <template x-if="hasTechnical">
                        <div class="space-y-4">
                            <div class="flex justify-end">
                                <button type="button" @click="copyTechnical()"
                                        class="px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-200 dark:border-gray-700 text-gray-700 dark:text-gray-300 hover:border-primary-400 hover:text-primary-600">
                                    Copy for a bug report
                                </button>
                            </div>

                            <template x-if="detail.technical.exception">
                                <div class="rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-900/20 p-3">
                                    <p class="font-mono text-sm font-semibold text-red-800 dark:text-red-300 break-all" x-text="detail.technical.exception"></p>
                                    <p class="text-sm text-red-700 dark:text-red-400 mt-1 break-words" x-text="detail.technical.exception_message"></p>
                                    <p class="font-mono text-xs text-red-600 dark:text-red-500 mt-1.5 break-all" x-text="detail.technical.file"></p>
                                </div>
                            </template>

                            <template x-if="detail.technical.sql">
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Failing query</h4>
                                    <pre class="rounded-lg bg-gray-900 text-gray-100 p-3 text-xs overflow-x-auto whitespace-pre-wrap break-all" x-text="detail.technical.sql"></pre>
                                    <template x-if="detail.technical.sql_bindings">
                                        <pre class="mt-1.5 rounded-lg bg-gray-50 dark:bg-gray-800 p-2 text-xs overflow-x-auto text-gray-600 dark:text-gray-400" x-text="pretty(detail.technical.sql_bindings)"></pre>
                                    </template>
                                </div>
                            </template>

                            <template x-if="detail.technical.exception_chain">
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Caused by</h4>
                                    <ol class="space-y-1.5">
                                        <template x-for="(cause, i) in detail.technical.exception_chain" :key="i">
                                            <li class="rounded-lg border border-gray-200 dark:border-gray-800 px-3 py-2">
                                                <p class="font-mono text-xs font-medium text-gray-900 dark:text-white break-all" x-text="cause.class"></p>
                                                <p class="text-xs text-gray-600 dark:text-gray-400 break-words" x-text="cause.message"></p>
                                                <p class="font-mono text-[11px] text-gray-400 break-all" x-text="cause.at"></p>
                                            </li>
                                        </template>
                                    </ol>
                                </div>
                            </template>

                            <template x-if="detail.technical.trace">
                                <div>
                                    <h4 class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2">Stack trace</h4>
                                    <pre class="rounded-lg bg-gray-900 text-gray-300 p-3 text-[11px] leading-relaxed overflow-x-auto" x-text="detail.technical.trace"></pre>
                                </div>
                            </template>

                            <template x-if="detail.technical.headers">
                                <details class="rounded-lg border border-gray-200 dark:border-gray-800">
                                    <summary class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer">Request headers</summary>
                                    <pre class="px-3 pb-3 text-xs overflow-x-auto text-gray-600 dark:text-gray-400" x-text="pretty(detail.technical.headers)"></pre>
                                </details>
                            </template>

                            <details class="rounded-lg border border-gray-200 dark:border-gray-800">
                                <summary class="px-3 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 cursor-pointer">Everything captured</summary>
                                <pre class="px-3 pb-3 text-xs overflow-x-auto text-gray-600 dark:text-gray-400" x-text="pretty(detail.raw_data)"></pre>
                            </details>
                        </div>
                    </template>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
