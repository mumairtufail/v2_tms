{{-- Shown instead of the delete confirmation when a customer has orders. Expects $customer (with orders_count) and $return ('index' or 'details'). --}}
@php
    $orderCount = $customer->orders_count ?? $customer->orders()->count();
    $canMarkInactive = $customer->is_active && auth()->user()->hasPermission('customers', 'update');
@endphp

<x-confirm-modal name="customer-has-orders-{{ $customer->id }}" title="This customer can't be deleted">
    <p class="text-sm text-gray-600 dark:text-gray-400">
        <strong class="text-gray-900 dark:text-white">{{ $customer->name }}</strong> has
        {{ $orderCount }} {{ \Illuminate\Support\Str::plural('order', $orderCount) }}. Deleting it would leave those orders without a customer.
    </p>
    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
        @if($customer->is_active)
            Mark it inactive instead: it can't be picked on new orders, and its order history stays.
        @else
            It's already inactive, so it can't be picked on new orders.
        @endif
    </p>
    <x-slot name="footer">
        <button type="button" @click="$dispatch('close-modal', 'customer-has-orders-{{ $customer->id }}')" class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-gray-800 hover:bg-gray-200 dark:hover:bg-gray-700 rounded-lg">
            {{ $canMarkInactive ? 'Cancel' : 'Close' }}
        </button>
        @if($canMarkInactive)
        <form action="{{ route('v2.customers.deactivate', ['company' => $company->slug, 'customer' => $customer->id]) }}" method="POST">
            @csrf @method('PATCH')
            <input type="hidden" name="return" value="{{ $return }}">
            <button type="submit" class="px-3 py-1.5 text-sm bg-primary-600 hover:bg-primary-700 text-white rounded-lg">Mark inactive</button>
        </form>
        @endif
    </x-slot>
</x-confirm-modal>
