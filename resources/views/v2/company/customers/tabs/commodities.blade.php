{{-- Commodities tab --}}
@php
    use App\Models\CustomerCommodity;

    $cell = 'block w-full min-w-0 h-7 px-1.5 text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded focus:border-primary-500 focus:ring-primary-500';
    $selectCell = 'block w-full min-w-0 h-7 py-0 pl-1.5 pr-5 text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded focus:border-primary-500 focus:ring-primary-500 bg-[position:right_0.15rem_center]';
    $iconButton = 'inline-flex items-center justify-center w-7 h-7 rounded-md transition-colors';
    // Actions stay pinned to the right edge while the rest of the row scrolls.
    $stickyCell = 'sticky right-0 z-[1] border-l border-gray-100 dark:border-gray-800';

    // Which row is open for editing: after a validation error, or the add row after adding one.
    $editingOld = old('_form') === 'commodity' ? (old('_commodity_id') ?: 'new') : (session('commodity_keep_adding') ? 'new' : null);
    $isOld = fn ($key) => old('_form') === 'commodity' && (string) (old('_commodity_id') ?: 'new') === (string) $key;
    $number = fn ($v) => $v === null || $v === '' ? '' : rtrim(rtrim(number_format((float) $v, 2, '.', ''), '0'), '.');
    $field = fn (string $name, ?CustomerCommodity $commodity) => $isOld($commodity?->id ?? 'new')
        ? old($name)
        : (in_array($name, ['volume', 'weight', 'linear_feet', 'length', 'width', 'height'], true) ? $number($commodity?->{$name}) : $commodity?->{$name});
    // null = the add row. A new collection, so $commodities keeps its real count.
    $rows = collect([null])->merge($commodities);
    $measures = ['volume' => 'Vol', 'weight' => 'WT', 'linear_feet' => 'LF', 'length' => 'LG', 'width' => 'WD', 'height' => 'HT'];
@endphp

<div x-data="{
        editing: @js($editingOld),
        importOpen: {{ $errors->has('file') ? 'true' : 'false' }},
        selected: [],
        ids: @js($commodities->pluck('id')->values()),
        confirmAction: '',
        confirmTitle: '',
        confirmBody: '',
        confirmIds: [],
        askDelete(action, description) {
            this.confirmAction = action;
            this.confirmIds = [];
            this.confirmTitle = 'Delete commodity';
            this.confirmBody = `Delete “${description}”? This can't be undone.`;
            this.$dispatch('open-modal', 'confirm-commodity-delete');
        },
        askBulkDelete(action) {
            this.confirmAction = action;
            this.confirmIds = [...this.selected];
            this.confirmTitle = this.selected.length === 1 ? 'Delete commodity' : 'Delete commodities';
            this.confirmBody = `Delete ${this.selected.length} selected ${this.selected.length === 1 ? 'commodity' : 'commodities'}? This can't be undone.`;
            this.$dispatch('open-modal', 'confirm-commodity-delete');
        },
        open(key) {
            this.editing = key;
            this.$nextTick(() => document.getElementById('commodity-' + key + '-description')?.focus());
        },
        toggleAll(on) { this.selected = on ? [...this.ids] : []; },
     }"
     x-init="if (editing !== null) open(editing)"
     @keydown.escape="editing = null">

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center gap-2 mb-3">
        <form method="GET" action="{{ route('v2.customers.show', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="relative w-full sm:w-60">
            <input type="hidden" name="tab" value="commodities">
            <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input type="search" name="q" value="{{ $search }}" placeholder="Search commodities"
                   class="w-full h-8 pl-8 pr-2 text-xs border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500">
        </form>

        @if($canEdit)
        <button type="button" x-show="selected.length" x-cloak
                @click="askBulkDelete('{{ route('v2.customers.commodities.bulk-destroy', ['company' => $company->slug, 'customer' => $customer->id]) }}')"
                class="inline-flex items-center gap-1.5 h-8 px-2.5 rounded-lg border border-red-200 dark:border-red-900/60 text-xs font-medium text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            <span>Delete selected (<span x-text="selected.length"></span>)</span>
        </button>
        @endif

        <div class="flex items-center gap-2 sm:ml-auto">
            {{-- While rows are selected, the bulk action takes the CSV buttons' place so the toolbar stays on one line --}}
            @if($canEdit)
            <button type="button" x-show="!selected.length" @click="importOpen = !importOpen" class="{{ $secondaryButton }} h-8 px-2.5 text-xs">Upload CSV</button>
            @endif
            <a x-show="!selected.length" href="{{ route('v2.customers.commodities.export', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="{{ $secondaryButton }} h-8 px-2.5 text-xs">Download CSV</a>
            @if($canEdit)
            <button type="button" @click="open('new')" class="{{ $primaryButton }} h-8 px-2.5 text-xs">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                Add commodity
            </button>
            @endif
        </div>
    </div>

    @if($canEdit)
    <form x-show="importOpen" x-cloak method="POST" enctype="multipart/form-data"
          action="{{ route('v2.customers.commodities.import', ['company' => $company->slug, 'customer' => $customer->id]) }}"
          class="mb-3 flex flex-wrap items-center gap-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/40 px-3 py-2">
        @csrf
        <input type="file" name="file" accept=".csv,text/csv" required class="text-xs text-gray-700 dark:text-gray-300 file:mr-2 file:h-7 file:px-2.5 file:rounded-md file:border-0 file:bg-white dark:file:bg-gray-700 file:text-xs file:font-medium file:text-gray-700 dark:file:text-gray-200">
        <button type="submit" class="{{ $primaryButton }} h-7 px-2.5 text-xs">Import</button>
        <a href="{{ route('v2.customers.commodities.template', ['company' => $company->slug, 'customer' => $customer->id]) }}" class="text-xs font-medium text-primary-600 hover:underline">Download template</a>
        <span class="text-[11px] text-gray-500">Rows are added to the list. Units: in/lbs or cm/kg.</span>
        @error('file')<p class="w-full text-xs text-red-600">{{ $message }}</p>@enderror
    </form>
    @endif

    @if($importErrors = session('commodity_import_errors'))
    <div class="mb-3 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/10 px-3 py-2">
        <p class="text-xs font-medium text-amber-800 dark:text-amber-300">Some rows weren't imported</p>
        <ul class="mt-1 list-disc list-inside text-[11px] text-amber-800 dark:text-amber-300 space-y-0.5">
            @foreach($importErrors as $error)<li>{{ $error }}</li>@endforeach
        </ul>
    </div>
    @endif

    {{-- One form per editable row; inputs join it through the form attribute so the table stays valid HTML. --}}
    @foreach($rows as $commodity)
    @php $key = $commodity?->id ?? 'new'; @endphp
    <form id="commodity-form-{{ $key }}" method="POST"
          action="{{ $commodity ? route('v2.customers.commodities.update', ['company' => $company->slug, 'customer' => $customer->id, 'commodity' => $commodity->id]) : route('v2.customers.commodities.store', ['company' => $company->slug, 'customer' => $customer->id]) }}">
        @csrf
        @if($commodity) @method('PUT') @endif
        <input type="hidden" name="_form" value="commodity">
        <input type="hidden" name="_commodity_id" value="{{ $commodity?->id }}">
    </form>
    @endforeach

    {{-- contain: keeps the wide table scrolling inside this box instead of widening the page on phones --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 [contain:inline-size_layout_paint]">
        <table class="w-full min-w-[1040px] text-xs">
            <thead class="bg-gray-50 dark:bg-gray-800/60">
                <tr class="text-left text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
                    @if($canEdit)
                    <th class="w-8 pl-3 pr-1 py-2">
                        <input type="checkbox" :checked="ids.length > 0 && selected.length === ids.length" @change="toggleAll($event.target.checked)" :disabled="ids.length === 0"
                               aria-label="Select all commodities" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-3.5 h-3.5">
                    </th>
                    @endif
                    <th class="px-2 py-2 min-w-[11rem]">Description <span class="text-red-500">*</span></th>
                    <th class="px-2 py-2 w-28">Type</th>
                    <th class="px-2 py-2 w-24">Unit</th>
                    @foreach($measures as $name => $heading)
                    <th class="px-2 py-2 {{ $name === 'weight' ? 'w-20' : 'w-14' }} text-right">{{ $heading }}</th>
                    @endforeach
                    <th class="px-2 py-2 w-16">Class</th>
                    <th class="px-2 py-2 w-20">NMFC</th>
                    <th class="px-2 py-2 w-24">SKU</th>
                    <th class="{{ $stickyCell }} w-[4.5rem] px-2 py-2 bg-gray-50 dark:bg-gray-800"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($rows as $commodity)
                @php $key = $commodity?->id ?? 'new'; $formId = 'commodity-form-' . $key; @endphp

                {{-- Read-only row --}}
                @if($commodity)
                <tr x-show="editing !== {{ $commodity->id }}" class="group hover:bg-gray-50 dark:hover:bg-gray-800/40" :class="selected.includes({{ $commodity->id }}) ? 'bg-primary-50/50 dark:bg-primary-900/10' : ''">
                    @if($canEdit)
                    <td class="pl-3 pr-1 py-1.5">
                        <input type="checkbox" :value="{{ $commodity->id }}" x-model="selected" aria-label="Select {{ $commodity->description }}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500 w-3.5 h-3.5">
                    </td>
                    @endif
                    <td class="px-2 py-1.5 font-medium text-gray-900 dark:text-white">{{ $commodity->description }}</td>
                    <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300">{{ CustomerCommodity::TYPES[$commodity->type] ?? '—' }}</td>
                    <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300">{{ CustomerCommodity::UNITS[$commodity->measurement_unit] ?? '' }}</td>
                    @foreach($measures as $name => $heading)
                    @php $shown = $number($commodity->{$name}); @endphp
                    <td class="px-2 py-1.5 text-right tabular-nums whitespace-nowrap {{ $shown === '' ? 'text-gray-300 dark:text-gray-600' : 'text-gray-700 dark:text-gray-300' }}">
                        {{ $shown === '' ? '—' : ($name === 'weight' ? $shown . ' ' . $commodity->weightUnit() : $shown) }}
                    </td>
                    @endforeach
                    <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300">{{ $commodity->freight_class ?: '—' }}</td>
                    <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300">{{ $commodity->nmfc ?: '—' }}</td>
                    <td class="px-2 py-1.5 text-gray-600 dark:text-gray-300 truncate max-w-[6rem]" title="{{ $commodity->sku }}">{{ $commodity->sku ?: '—' }}</td>
                    <td class="{{ $stickyCell }} px-1 py-1 bg-white dark:bg-gray-900 group-hover:bg-gray-50 dark:group-hover:bg-gray-800 text-right whitespace-nowrap">
                        @if($canEdit)
                        <button type="button" @click="open({{ $commodity->id }})" class="{{ $iconButton }} text-gray-400 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20" title="Edit {{ $commodity->description }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </button>
                        <button type="button" @click="askDelete('{{ route('v2.customers.commodities.destroy', ['company' => $company->slug, 'customer' => $customer->id, 'commodity' => $commodity->id]) }}', @js($commodity->description))"
                                class="{{ $iconButton }} text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20" title="Delete {{ $commodity->description }}">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                        @endif
                    </td>
                </tr>
                @endif

                {{-- Add / edit row --}}
                @if($canEdit)
                <tr x-show="editing === @js($key)" x-cloak class="bg-primary-50 dark:bg-gray-800">
                    <td class="pl-3 pr-1 py-1.5 text-[10px] font-semibold uppercase text-primary-700 dark:text-primary-300">{{ $commodity ? '' : 'New' }}</td>
                    <td class="px-2 py-1.5">
                        <input id="commodity-{{ $key }}-description" form="{{ $formId }}" type="text" name="description" value="{{ $field('description', $commodity) }}" placeholder="Description" required class="{{ $cell }}">
                    </td>
                    <td class="px-2 py-1.5">
                        <select form="{{ $formId }}" name="type" class="{{ $selectCell }} min-w-[6rem]">
                            <option value="">—</option>
                            @foreach(CustomerCommodity::TYPES as $typeValue => $typeName)
                            <option value="{{ $typeValue }}" @selected($isOld($key) ? old('type') === $typeValue : ($commodity ? $commodity->type === $typeValue : $typeValue === 'skid'))>{{ $typeName }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-2 py-1.5">
                        <select form="{{ $formId }}" name="measurement_unit" class="{{ $selectCell }} min-w-[5rem]">
                            @foreach(CustomerCommodity::UNITS as $unitValue => $unitName)
                            <option value="{{ $unitValue }}" @selected(($field('measurement_unit', $commodity) ?? 'in_lbs') === $unitValue)>{{ $unitName }}</option>
                            @endforeach
                        </select>
                    </td>
                    @foreach($measures as $name => $heading)
                    <td class="px-1 py-1.5">
                        <input form="{{ $formId }}" type="text" inputmode="decimal" name="{{ $name }}" value="{{ $field($name, $commodity) }}" placeholder="0" aria-label="{{ $heading }}" class="{{ $cell }} text-right tabular-nums">
                    </td>
                    @endforeach
                    <td class="px-1 py-1.5">
                        <select form="{{ $formId }}" name="freight_class" aria-label="Class" class="{{ $selectCell }}">
                            <option value="">—</option>
                            @foreach(CustomerCommodity::FREIGHT_CLASSES as $class)
                            <option value="{{ $class }}" @selected((string) $field('freight_class', $commodity) === $class)>{{ $class }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td class="px-1 py-1.5"><input form="{{ $formId }}" type="text" name="nmfc" value="{{ $field('nmfc', $commodity) }}" placeholder="NMFC" class="{{ $cell }}"></td>
                    <td class="px-1 py-1.5"><input form="{{ $formId }}" type="text" name="sku" value="{{ $field('sku', $commodity) }}" placeholder="SKU" class="{{ $cell }}"></td>
                    <td class="{{ $stickyCell }} px-1 py-1 bg-primary-50 dark:bg-gray-800 text-right whitespace-nowrap">
                        <button form="{{ $formId }}" type="submit" class="{{ $iconButton }} bg-primary-600 hover:bg-primary-700 text-white" title="{{ $commodity ? 'Save' : 'Add' }} (Enter)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span class="sr-only">{{ $commodity ? 'Save' : 'Add' }}</span>
                        </button>
                        <button type="button" @click="editing = null" class="{{ $iconButton }} text-gray-500 hover:text-gray-800 hover:bg-white dark:hover:bg-gray-700" title="Cancel (Esc)">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                            <span class="sr-only">Cancel</span>
                        </button>
                    </td>
                </tr>
                @if($isOld($key) && $errors->any())
                <tr x-show="editing === @js($key)">
                    <td colspan="{{ $canEdit ? 14 : 13 }}" class="px-3 py-1.5 text-xs text-red-600 bg-red-50 dark:bg-red-900/10">{{ $errors->first() }}</td>
                </tr>
                @endif
                @endif
                @endforeach

                @if($commodities->count() === 0)
                <tr x-show="editing !== 'new'">
                    <td colspan="{{ $canEdit ? 14 : 13 }}" class="px-3 py-8 text-center">
                        @if($search !== '')
                        <p class="text-sm text-gray-600 dark:text-gray-300">No commodities match "{{ $search }}".</p>
                        @else
                        <p class="text-sm font-medium text-gray-900 dark:text-white">No saved commodities yet</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Add the goods this customer ships often, or upload them from a CSV.</p>
                        @endif
                    </td>
                </tr>
                @endif
            </tbody>
        </table>
    </div>

    @if($canEdit)
    @include('v2.company.customers.partials.confirm-delete-modal', ['name' => 'confirm-commodity-delete'])
    @endif

    <p class="mt-2 text-[11px] text-gray-400">
        {{ $commodities->count() === 1 ? '1 commodity' : $commodities->count() . ' commodities' }}
        · Typing one of these descriptions on an order fills in the rest of the row.
        @if($canEdit) Press Enter to save a row, Esc to cancel. @endif
    </p>
</div>
