@php
    $isConsignee = $prefix === 'consignee';
@endphp

<div class="grid grid-cols-1 sm:grid-cols-2 gap-3 p-4 bg-white dark:bg-gray-800/50 rounded-lg border border-gray-100 dark:border-gray-800 shadow-sm">
    <!-- Company Name -->
    <div class="sm:col-span-2">
        <x-input-label :for="`stops_${index}_${prefix}_company_name`" value="Company Name" class="text-[10px] uppercase text-gray-400" />
        <x-text-input :id="`stops_${index}_${prefix}_company_name`" 
                    :name="`stops[${index}][${prefix}_company_name]`" 
                    type="text" 
                    class="mt-0.5 block w-full text-sm border-gray-200 shadow-none focus:border-primary-500 focus:ring-0" 
                    x-model="stop.{{ $prefix }}.company_name" 
                    placeholder="Enter company name..." />
    </div>

    <!-- Address 1 -->
    <div class="sm:col-span-2">
        <x-input-label :for="`stops_${index}_${prefix}_address_1`" value="Address" class="text-[10px] uppercase text-gray-400" />
        <x-text-input :id="`stops_${index}_${prefix}_address_1`" 
                    :name="`stops[${index}][${prefix}_address_1]`" 
                    type="text" 
                    class="mt-0.5 block w-full text-sm border-gray-200 shadow-none focus:border-primary-500 focus:ring-0" 
                    x-model="stop.{{ $prefix }}.address_1" 
                    placeholder="Street address" />
    </div>

    <!-- City -->
    <div>
        <x-input-label :for="`stops_${index}_${prefix}_city`" value="City" class="text-[10px] uppercase text-gray-400" />
        <x-text-input :id="`stops_${index}_${prefix}_city`" 
                    :name="`stops[${index}][${prefix}_city]`" 
                    type="text" 
                    class="mt-0.5 block w-full text-sm border-gray-200 shadow-none focus:border-primary-500 focus:ring-0" 
                    x-model="stop.{{ $prefix }}.city" 
                    placeholder="City" />
    </div>

    <!-- State & Zip -->
    <div class="grid grid-cols-2 gap-2">
        <div>
            <x-input-label :for="`stops_${index}_${prefix}_state`" value="State" class="text-[10px] uppercase text-gray-400" />
            <x-text-input :id="`stops_${index}_${prefix}_state`" 
                        :name="`stops[${index}][${prefix}_state]`" 
                        type="text" 
                        class="mt-0.5 block w-full text-sm border-gray-200 shadow-none focus:border-primary-500 focus:ring-0" 
                        x-model="stop.{{ $prefix }}.state" 
                        placeholder="ST" />
        </div>
        <div>
            <x-input-label :for="`stops_${index}_${prefix}_zip`" value="Zip" class="text-[10px] uppercase text-gray-400" />
            <x-text-input :id="`stops_${index}_${prefix}_zip`" 
                        :name="`stops[${index}][${prefix}_zip]`" 
                        type="text" 
                        class="mt-0.5 block w-full text-sm border-gray-200 shadow-none focus:border-primary-500 focus:ring-0" 
                        x-model="stop.{{ $prefix }}.zip" 
                        placeholder="12345" />
        </div>
    </div>

    <!-- Contacts (Collapsible optionally, but for now flat) -->
    <div class="sm:col-span-2 pt-2 border-t border-gray-50 dark:border-gray-800 mt-2">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <x-input-label :for="`stops_${index}_${prefix}_contact_name`" value="Contact Name" class="text-[10px] uppercase text-gray-400" />
                <x-text-input :id="`stops_${index}_${prefix}_contact_name`" 
                            :name="`stops[${index}][${prefix}_contact_name]`" 
                            type="text" 
                            class="mt-0.5 block w-full text-xs border-gray-100" 
                            x-model="stop.{{ $prefix }}.contact_name" />
            </div>
            <div>
                <x-input-label :for="`stops_${index}_${prefix}_phone`" value="Phone" class="text-[10px] uppercase text-gray-400" />
                <x-text-input :id="`stops_${index}_${prefix}_phone`" 
                            :name="`stops[${index}][${prefix}_phone]`" 
                            type="text" 
                            class="mt-0.5 block w-full text-xs border-gray-100" 
                            x-model="stop.{{ $prefix }}.phone" />
            </div>
        </div>
    </div>

    <!-- Times / Appointment -->
    <div class="sm:col-span-2 flex items-center justify-between pt-2">
         <div class="flex items-center gap-4">
            <div>
                <x-input-label value="Opening" class="text-[8px] uppercase text-gray-400" />
                <input type="time" :name="`stops[${index}][${prefix}_opening_time]`" x-model="stop.{{ $prefix }}.opening_time" class="block w-full border-0 p-0 text-xs bg-transparent dark:text-white focus:ring-0">
            </div>
            <div>
                <x-input-label value="Closing" class="text-[8px] uppercase text-gray-400" />
                <input type="time" :name="`stops[${index}][${prefix}_closing_time]`" x-model="stop.{{ $prefix }}.closing_time" class="block w-full border-0 p-0 text-xs bg-transparent dark:text-white focus:ring-0">
            </div>
         </div>
         <label class="flex items-center gap-2 cursor-pointer">
            <input type="checkbox" :name="`stops[${index}][${prefix}_appointment]`" x-model="stop.{{ $prefix }}.appointment" class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 w-3 h-3">
            <span class="text-[10px] font-bold text-gray-500 uppercase">Appt Required</span>
         </label>
    </div>
</div>
