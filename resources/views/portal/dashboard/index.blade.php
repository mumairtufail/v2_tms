@extends('portal.layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="mb-8">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Welcome, {{ $customer->name }}</h1>
    <p class="text-gray-500 dark:text-gray-400 mt-1">Overview of your shipments with {{ $company->name }}</p>
</div>

<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $stats['total'] }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Total Orders</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-2xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['booked'] }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Booked</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-2xl font-bold text-amber-600 dark:text-amber-400">{{ $stats['in_transit'] }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">In Transit</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
        <h3 class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $stats['delivered'] }}</h3>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Delivered</p>
    </div>
</div>

<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-8">
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3">
        <span class="text-xs text-gray-500 dark:text-gray-400">Draft</span>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['draft'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3">
        <span class="text-xs text-gray-500 dark:text-gray-400">New / Quoted</span>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['new'] + $stats['quoted'] }}</p>
    </div>
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 px-4 py-3">
        <span class="text-xs text-gray-500 dark:text-gray-400">Quoted</span>
        <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ $stats['quoted'] }}</p>
    </div>
</div>

<div class="flex flex-col sm:flex-row justify-end gap-3">
    <form method="POST" action="{{ route('portal.orders.store', ['company' => $company->slug]) }}">
        @csrf
        <button type="submit" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-primary-200 dark:border-primary-800 text-primary-700 dark:text-primary-300 hover:bg-primary-50 dark:hover:bg-primary-900/20 text-sm font-medium rounded-lg transition-colors">
            New Order
        </button>
    </form>
    <a href="{{ route('portal.orders.index', ['company' => $company->slug]) }}"
       class="inline-flex items-center px-4 py-2 bg-primary-600 hover:bg-primary-500 text-white text-sm font-medium rounded-lg transition-colors">
        View All Orders
    </a>
</div>
@endsection
