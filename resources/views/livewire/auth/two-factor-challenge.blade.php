<div>
    <div class="mb-8 text-center">
        <div class="w-14 h-14 bg-primary-100 dark:bg-primary-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Two-Factor Authentication</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
            @if(!$use_recovery)
                Enter the 6-digit code from your authenticator app
            @else
                Enter one of your recovery codes
            @endif
        </p>
    </div>

    <div class="space-y-5">
        @if(!$use_recovery)
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Authentication Code</label>
                <input
                    wire:model="code"
                    wire:keydown.enter="authenticate"
                    type="text"
                    inputmode="numeric"
                    maxlength="6"
                    placeholder="000 000"
                    autofocus
                    class="w-full px-4 py-3 text-center text-2xl tracking-[0.5em] font-mono border rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-colors"
                >
                @error('code')
                <p class="mt-1.5 text-sm text-red-500 text-center">{{ $message }}</p>
                @enderror
            </div>
        @else
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Recovery Code</label>
                <input
                    wire:model="recovery_code"
                    wire:keydown.enter="authenticate"
                    type="text"
                    placeholder="XXXX-XXXX"
                    autofocus
                    class="w-full px-4 py-3 text-center font-mono border rounded-xl bg-gray-50 dark:bg-gray-800 border-gray-200 dark:border-gray-700 text-gray-900 dark:text-white focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 transition-colors uppercase"
                >
                @error('recovery_code')
                <p class="mt-1.5 text-sm text-red-500 text-center">{{ $message }}</p>
                @enderror
            </div>
        @endif

        <button
            wire:click="authenticate"
            wire:loading.attr="disabled"
            class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 disabled:opacity-60 text-white font-semibold rounded-xl transition-colors"
        >
            <span wire:loading.remove wire:target="authenticate">Verify &amp; Sign In</span>
            <span wire:loading wire:target="authenticate" class="flex items-center gap-2">
                <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                Verifying…
            </span>
        </button>

        <button
            wire:click="$toggle('use_recovery')"
            type="button"
            class="w-full text-sm text-center text-gray-500 hover:text-primary-600 dark:hover:text-primary-400 transition-colors"
        >
            @if(!$use_recovery)
                Lost access to your app? Use a recovery code
            @else
                &larr; Back to authentication code
            @endif
        </button>
    </div>
</div>
