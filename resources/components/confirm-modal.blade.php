@props(['name' => 'confirm-modal'])

<div
    x-data="{
        show: false,
        title: '',
        message: '',
        action: '',
        method: 'DELETE',
        confirmText: 'Delete',
        cancelText: 'Cancel',
        type: 'danger', // danger, warning, info
        
        open(detail) {
            this.show = true;
            this.title = detail.title || 'Are you sure?';
            this.message = detail.message || 'This action cannot be undone.';
            this.action = detail.action || '#';
            this.method = detail.method || 'DELETE';
            this.confirmText = detail.confirmText || 'Delete';
            this.cancelText = detail.cancelText || 'Cancel';
            this.type = detail.type || 'danger';
            
            document.body.classList.add('overflow-y-hidden');
        },
        
        close() {
            this.show = false;
            document.body.classList.remove('overflow-y-hidden');
        }
    }"
    x-on:open-confirm-modal.window="open($event.detail)"
    x-on:keydown.escape.window="close()"
    x-show="show"
    class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-0"
    style="display: none;"
>
    <!-- Backdrop -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 transition-all transform"
        @click="close()"
    >
        <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm"></div>
    </div>

    <!-- Modal Panel -->
    <div 
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="relative w-full max-w-lg transform overflow-hidden rounded-2xl bg-white dark:bg-gray-900 text-left shadow-2xl transition-all sm:my-8 border border-gray-100 dark:border-gray-800"
    >
        <div class="p-6">
            <div class="flex items-start gap-4">
                <!-- Icon -->
                <div class="flex-shrink-0">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100 dark:bg-red-900/30 sm:h-10 sm:w-10" :class="{
                        'bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400': type === 'danger',
                        'bg-amber-100 dark:bg-amber-900/30 text-amber-600 dark:text-amber-400': type === 'warning',
                        'bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400': type === 'info'
                    }">
                        <svg x-show="type === 'danger'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                        </svg>
                        <svg x-show="type === 'warning'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
                        </svg>
                        <svg x-show="type === 'info'" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 pt-0.5">
                    <h3 class="text-lg font-semibold leading-6 text-gray-900 dark:text-white" x-text="title"></h3>
                    <div class="mt-2">
                        <p class="text-sm text-gray-500 dark:text-gray-400" x-text="message"></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-4 flex flex-row-reverse gap-3">
            <form :action="action" method="POST" class="inline-block">
                @csrf
                <input type="hidden" name="_method" :value="method">
                <button 
                    type="submit" 
                    class="inline-flex w-full justify-center rounded-lg px-4 py-2.5 text-sm font-semibold text-white shadow-sm sm:w-auto transition-all duration-200"
                    :class="{
                        'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-red-500/20': type === 'danger',
                        'bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 shadow-amber-500/20': type === 'warning',
                        'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-blue-500/20': type === 'info'
                    }"
                    x-text="confirmText"
                ></button>
            </form>
            <button 
                type="button" 
                class="inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-2.5 text-sm font-semibold text-gray-900 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 sm:w-auto transition-colors"
                @click="close()"
                x-text="cancelText"
            ></button>
        </div>
    </div>
</div>
