{{-- Accessorials tab --}}
@php
    use App\Support\AccessorialCategories;

    $grouped = $accessorials->groupBy('category');
    $idsByCategory = $grouped->map(fn ($group) => $group->pluck('id')->map(fn ($id) => (string) $id)->values());
@endphp

<form method="POST" action="{{ route('v2.customers.accessorials.update', ['company' => $company->slug, 'customer' => $customer->id]) }}"
      x-data="{
          selected: @js(array_map('strval', $enabledIds)),
          groups: @js($idsByCategory),
          filter: '',
          dirty: false,
          setGroup(ids, on) {
              ids.forEach(id => {
                  const i = this.selected.indexOf(id);
                  if (on && i === -1) this.selected.push(id);
                  if (!on && i !== -1) this.selected.splice(i, 1);
              });
              this.dirty = true;
          },
          groupChecked(ids) { return ids.length > 0 && ids.every(id => this.selected.includes(id)); },
          matches(name) { return !this.filter || name.includes(this.filter.toLowerCase()); },
      }"
      @change="dirty = true">
    @csrf
    @method('PUT')

    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">
            <span class="font-medium text-gray-900 dark:text-white tabular-nums" x-text="selected.length"></span> of {{ $accessorials->count() }} enabled. Accessorials already on an order stay there.
        </p>
        <div class="flex flex-wrap items-center gap-2">
            <input type="search" x-model="filter" placeholder="Filter accessorials"
                   class="h-9 w-48 px-3 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-lg placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500">
            @if($canEdit)
            <button type="button" @click="setGroup(Object.values(groups).flat(), true)" class="{{ $secondaryButton }} h-9 text-xs">Select all</button>
            <button type="button" @click="setGroup(Object.values(groups).flat(), false)" class="{{ $secondaryButton }} h-9 text-xs">Clear all</button>
            @endif
        </div>
    </div>

    @if($accessorials->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-6 py-10 text-center">
        <p class="text-sm text-gray-600 dark:text-gray-300">This company has no accessorials set up yet.</p>
    </div>
    @else
    <fieldset @disabled(! $canEdit) class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 [contain:inline-size_layout_paint]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr class="text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-2">Accessorial</th>
                    <th class="px-4 py-2">Category</th>
                    <th class="px-4 py-2 w-24 text-center">Enabled</th>
                </tr>
            </thead>
            @foreach([AccessorialCategories::ORIGIN, AccessorialCategories::DESTINATION, AccessorialCategories::END_TO_END] as $category)
            @continue(! $grouped->has($category))
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800 border-t border-gray-200 dark:border-gray-800">
                <tr class="bg-gray-50/60 dark:bg-gray-800/30">
                    <th scope="rowgroup" colspan="2" class="px-4 py-1.5 text-left text-[11px] font-semibold uppercase text-gray-500">
                        {{ AccessorialCategories::label($category) }} <span class="font-normal normal-case text-gray-400">· {{ $grouped[$category]->count() }}</span>
                    </th>
                    <td class="px-4 py-1.5 text-center">
                        <input type="checkbox" :checked="groupChecked(groups['{{ $category }}'])" @change="setGroup(groups['{{ $category }}'], $event.target.checked)"
                               aria-label="Enable all {{ AccessorialCategories::label($category) }} accessorials" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </td>
                </tr>
                @foreach($grouped[$category] as $accessorial)
                <tr x-show="matches('{{ strtolower(addslashes($accessorial->name)) }}')">
                    <td class="px-4 py-1.5 text-gray-800 dark:text-gray-200">
                        <label for="acc-{{ $accessorial->id }}" class="cursor-pointer">{{ $accessorial->name }}</label>
                        @unless($accessorial->is_active)<span class="ml-1 text-[10px] text-gray-400">Inactive</span>@endunless
                    </td>
                    <td class="px-4 py-1.5 text-gray-500 dark:text-gray-400">{{ $accessorial->categoryLabel() }}</td>
                    <td class="px-4 py-1.5 text-center">
                        <input id="acc-{{ $accessorial->id }}" type="checkbox" name="accessorial_ids[]" value="{{ $accessorial->id }}" x-model="selected"
                               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    </td>
                </tr>
                @endforeach
            </tbody>
            @endforeach
        </table>
    </fieldset>
    @endif

    @if($canEdit && $accessorials->isNotEmpty())
    <div class="sticky bottom-3 mt-4 flex items-center justify-between gap-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white/95 dark:bg-gray-900/95 backdrop-blur px-4 py-2.5 shadow-sm"
         x-show="dirty" x-transition.opacity x-cloak>
        <p class="text-sm text-gray-600 dark:text-gray-300">You have unsaved changes</p>
        <div class="flex gap-2">
            <a href="{{ $tabUrl('accessorials') }}" class="{{ $secondaryButton }} h-8 text-xs">Discard</a>
            <button type="submit" class="{{ $primaryButton }} h-8 text-xs">Save changes</button>
        </div>
    </div>
    @endif
</form>
