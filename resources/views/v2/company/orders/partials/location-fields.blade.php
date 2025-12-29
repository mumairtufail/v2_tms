{{-- Location Fields Partial for Order Form --}}
{{-- $prefix: 'shipper' or 'consignee' --}}

<div class="grid grid-cols-1 gap-4 p-4 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm">
    {{-- Row 1: Company Name --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Company Name</label>
        <input type="text" 
               x-model="stop.{{ $prefix }}.company_name" 
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Enter company name...">
    </div>

    {{-- Row 2: Address 1 --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Address 1</label>
        <input type="text" 
               x-model="stop.{{ $prefix }}.address_1" 
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Street address">
    </div>

    {{-- Row 3: Address 2 --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Address 2</label>
        <input type="text" 
               x-model="stop.{{ $prefix }}.address_2" 
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="Suite, unit, building, floor, etc.">
    </div>

    {{-- Row 4: City, State, Zip --}}
    <div class="grid grid-cols-3 gap-3">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">City</label>
            <input type="text" 
                   x-model="stop.{{ $prefix }}.city" 
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="City">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">State</label>
            <input type="text" 
                   x-model="stop.{{ $prefix }}.state" 
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="ST">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Zip</label>
            <input type="text" 
                   x-model="stop.{{ $prefix }}.zip" 
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
                   placeholder="12345">
        </div>
    </div>

    {{-- Row 5: Country --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Country</label>
        <select x-model="stop.{{ $prefix }}.country" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
            <option value="US">United States</option>
            <option value="CA">Canada</option>
            <option value="MX">Mexico</option>
        </select>
    </div>

    {{-- Row 6: Contact Name & Phone --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Contact Name</label>
            <input type="text" 
                   x-model="stop.{{ $prefix }}.contact_name" 
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Phone</label>
            <input type="text" 
                   x-model="stop.{{ $prefix }}.phone" 
                   class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
    </div>

    {{-- Row 7: Contact Email --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">Contact Email</label>
        <input type="email" 
               x-model="stop.{{ $prefix }}.email" 
               class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500"
               placeholder="email@example.com">
    </div>

    {{-- Row 8: Opening & Closing Times --}}
    <div class="grid grid-cols-2 gap-3">
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Opening</label>
            <input type="time" x-model="stop.{{ $prefix }}.opening_time" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
        <div>
            <label class="block text-[10px] font-medium text-gray-400 uppercase">Closing</label>
            <input type="time" x-model="stop.{{ $prefix }}.closing_time" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
        </div>
    </div>

    {{-- Row 9: Ready/Delivery Date & Time --}}
    <div class="pt-3 border-t border-gray-100 dark:border-gray-700">
        <label class="block text-[10px] font-bold text-gray-500 uppercase mb-2">{{ $prefix === 'shipper' ? 'Ready Date' : 'Requested Delivery' }}</label>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase">Date</label>
                <input type="date" x-model="stop.{{ $prefix }}.ready_date" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-400 uppercase">Time</label>
                <input type="time" x-model="stop.{{ $prefix }}.ready_time" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500">
            </div>
        </div>
        <label class="flex items-center gap-2 mt-2 cursor-pointer">
            <input type="checkbox" x-model="stop.{{ $prefix }}.appointment" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 w-4 h-4">
            <span class="text-xs text-gray-600 dark:text-gray-400">Make this an appointment</span>
        </label>
    </div>

    {{-- Row 10: Notes --}}
    <div>
        <label class="block text-[10px] font-medium text-gray-400 uppercase">{{ $prefix === 'shipper' ? 'Shipper Notes' : 'Consignee Notes' }}</label>
        <textarea x-model="stop.{{ $prefix }}.notes" rows="2" class="mt-0.5 block w-full text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-300 rounded-md focus:border-primary-500 focus:ring-primary-500" placeholder="Type any notes here..."></textarea>
    </div>
</div>
