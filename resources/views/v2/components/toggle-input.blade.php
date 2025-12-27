@props(['label', 'name', 'checked' => false, 'description' => null])

<div class="flex items-center justify-between py-2">
    <div class="flex-grow">
        <label for="{{ $name }}" class="text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
        </label>
        @if($description)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $description }}</p>
        @endif
    </div>
    <button 
        type="button"
        role="switch"
        x-data="{ enabled: {{ old($name, $checked) ? 'true' : 'false' }} }"
        @click="enabled = !enabled"
        :aria-checked="enabled"
        :class="enabled ? 'bg-primary-600' : 'bg-gray-200 dark:bg-gray-700'"
        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
    >
        <input type="hidden" name="{{ $name }}" :value="enabled ? '1' : '0'" x-model="enabled">
        <span :class="enabled ? 'translate-x-5' : 'translate-x-0'" class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"></span>
    </button>
</div>
