<x-confirm-modal name="delete-smtp-{{ $setting->id }}" title="Delete email account">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        Delete <strong class="text-gray-900 dark:text-white">{{ $setting->name }}</strong>? The saved password will be removed too.
    </p>
    @if($setting->is_active)
        <div class="mt-3 flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/20 p-3 text-xs leading-5 text-amber-800 dark:text-amber-300">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            This is your active account. After deleting it, emails use the system default until you set another account as active.
        </div>
    @endif
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'delete-smtp-{{ $setting->id }}')" class="px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">Cancel</button>
        <form action="{{ $smtpUrl('destroy', $setting) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="px-3 py-1.5 text-sm bg-red-600 hover:bg-red-700 text-white rounded-lg">Delete</button>
        </form>
    </x-slot>
</x-confirm-modal>
