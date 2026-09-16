{{-- Delete confirmation used by the customer tabs. Driven by the tab's Alpine state:
     confirmAction (form URL), confirmTitle, confirmBody and confirmIds (for bulk deletes).
     Expects $name — the modal name the tab dispatches. --}}
<x-modal :name="$name" maxWidth="md" focusable>
    <form method="POST" :action="confirmAction">
        @csrf
        @method('DELETE')
        <template x-for="id in confirmIds" :key="id"><input type="hidden" name="ids[]" :value="id"></template>

        <div class="px-5 pt-4 pb-3 flex items-start gap-3">
            <span class="shrink-0 grid place-items-center w-9 h-9 rounded-full bg-red-50 dark:bg-red-900/20 text-red-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </span>
            <div class="min-w-0">
                <h2 class="text-base font-semibold text-gray-900 dark:text-white" x-text="confirmTitle"></h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400" x-text="confirmBody"></p>
            </div>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex justify-end gap-2 rounded-b-lg">
            <button type="button" @click="$dispatch('close-modal', '{{ $name }}')" class="inline-flex items-center h-8 px-3 rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 text-xs font-medium text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700">Cancel</button>
            <button type="submit" class="inline-flex items-center h-8 px-3 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-medium">Delete</button>
        </div>
    </form>
</x-modal>
