<!-- Rate Confirmation Modal -->
<div x-show="showRateConfirmationModal" 
     class="v2-modal-backdrop fixed inset-0 z-50 flex items-start justify-center pt-10 pb-10 px-4 show"
     @click.self="closeRateConfirmationModal()"
     @keydown.escape.window="closeRateConfirmationModal()"
     x-cloak>
    
    <div class="v2-modal-content bg-white dark:bg-gray-900 rounded-2xl shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col h-full max-h-[90vh]">
        <!-- Header -->
        <div class="p-6 border-b border-gray-200 dark:border-gray-800 flex items-center justify-between bg-white dark:bg-gray-900 sticky top-0 z-10">
            <div>
                <h2 class="text-xl font-black text-gray-900 dark:text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Rate Confirmation # {{ $manifest->code }}
                </h2>
                <p class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest mt-1">Carrier Assignment Document</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="window.print()" class="p-2.5 text-gray-500 hover:text-primary-600 hover:bg-primary-50 dark:hover:bg-primary-900/20 rounded-xl transition-all" title="Print Document">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                </button>
                <button @click="closeRateConfirmationModal()" class="p-2.5 text-gray-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-xl transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Document Content -->
        <div class="flex-1 overflow-y-auto p-8 bg-gray-50 dark:bg-gray-950 print:bg-white print:p-0">
            <div class="bg-white dark:bg-gray-900 p-12 shadow-sm border border-gray-100 dark:border-gray-800 mx-auto w-full max-w-4xl print:shadow-none print:border-0 print:p-0">
                
                <!-- Logo & Title -->
                <div class="flex justify-between items-start mb-12 border-b-4 border-primary-600 pb-8">
                    <div>
                        <div class="text-3xl font-black text-gray-900 dark:text-white tracking-tighter">RATE CONFIRMATION</div>
                        <div class="text-primary-600 font-bold tracking-widest uppercase text-sm mt-1"># {{ $manifest->code }}</div>
                    </div>
                    <div class="text-right">
                        @if($company->logo)
                            <img src="{{ asset('storage/'.$company->logo) }}" alt="Logo" class="h-16 w-auto mb-2 ml-auto">
                        @else
                            <div class="h-16 w-48 bg-gray-100 dark:bg-gray-800 rounded flex items-center justify-center text-gray-400 font-bold italic border-2 border-dashed border-gray-200 dark:border-gray-700">Logo</div>
                        @endif
                    </div>
                </div>

                <!-- Billing & Carrier Info -->
                <div class="grid grid-cols-2 gap-12 mb-12">
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Billing Party</h3>
                        <div class="text-gray-900 dark:text-white font-black text-lg">{{ $company->name }}</div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm mt-2 leading-relaxed">
                            {{ $company->address }}<br>
                            {{ $company->city }}, {{ $company->state }} {{ $company->zip }}
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-6 text-xs">
                            <div>
                                <span class="text-gray-400 font-bold block uppercase tracking-wider">Date</span>
                                <span class="text-gray-900 dark:text-white font-bold">{{ date('M d, Y') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-400 font-bold block uppercase tracking-wider">Contact</span>
                                <span class="text-gray-900 dark:text-white font-bold">{{ auth()->user()->name }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Carrier</h3>
                        @if($manifest->carriers->count() > 0)
                            @foreach($manifest->carriers as $carrier)
                                <div class="text-gray-900 dark:text-white font-black text-lg">{{ $carrier->carrier_name }}</div>
                                <div class="text-gray-600 dark:text-gray-400 text-sm mt-2 leading-relaxed">
                                    {{ $carrier->address }}<br>
                                    {{ $carrier->city }}, {{ $carrier->state }} {{ $carrier->zip }}
                                </div>
                                <div class="mt-6 text-xs">
                                    <span class="text-gray-400 font-bold block uppercase tracking-wider">Carrier Ref #</span>
                                    <span class="text-gray-900 dark:text-white font-bold">N/A</span>
                                </div>
                            @endforeach
                        @else
                            <div class="text-red-500 font-bold italic px-4 py-3 bg-red-50 dark:bg-red-900/10 rounded-lg border border-red-100 dark:border-red-900/30 flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                                No Carrier Assigned
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Stops Section -->
                <div class="mb-12">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-6 border-b-2 border-gray-900 dark:border-white pb-2">Manifest Stops</h3>
                    
                    @if($manifest->stops->count() > 0)
                        <div class="space-y-8">
                            @foreach($manifest->stops as $index => $stop)
                                <div class="relative pl-12">
                                    <div class="absolute left-0 top-0 w-8 h-8 bg-gray-900 dark:bg-white text-white dark:text-gray-900 rounded flex items-center justify-center font-black text-sm">
                                        {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                    </div>
                                    <div class="grid grid-cols-2 gap-8">
                                        <div>
                                            <div class="font-black text-gray-900 dark:text-white uppercase mb-1">{{ $stop->location }}</div>
                                            <div class="text-sm text-gray-500 leading-relaxed">{{ $stop->address1 }}, {{ $stop->city }}, {{ $stop->state }} {{ $stop->postal }}</div>
                                        </div>
                                        <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded text-xs border border-gray-100 dark:border-gray-800">
                                            <div class="grid grid-cols-2 gap-y-3">
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase mb-0.5">Time</span>
                                                    <span class="text-gray-900 dark:text-white font-bold">{{ $stop->estimated_arrival ?? 'N/A' }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-400 font-bold block uppercase mb-0.5">Commodities</span>
                                                    <span class="text-gray-900 dark:text-white font-bold">{{ $manifest->freight ?? 'General Freight' }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <!-- Template Placeholder -->
                        <div class="space-y-8 opacity-40">
                             <div class="relative pl-12 border-l-2 border-dashed border-gray-200 dark:border-gray-700 ml-4">
                                <div class="absolute -left-4 top-0 w-8 h-8 bg-gray-200 dark:bg-gray-700 text-gray-500 rounded flex items-center justify-center font-black text-sm">01</div>
                                <div class="grid grid-cols-2 gap-8 py-2">
                                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-48 mb-2"></div>
                                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-full"></div>
                                </div>
                             </div>
                             <div class="relative pl-12 border-l-2 border-dashed border-gray-200 dark:border-gray-700 ml-4 pb-4">
                                <div class="absolute -left-4 top-0 w-8 h-8 bg-gray-200 dark:bg-gray-700 text-gray-500 rounded flex items-center justify-center font-black text-sm">02</div>
                                <div class="grid grid-cols-2 gap-8 py-2">
                                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-32 mb-2"></div>
                                    <div class="h-4 bg-gray-100 dark:bg-gray-800 rounded w-full"></div>
                                </div>
                             </div>
                        </div>
                    @endif
                </div>

                <!-- Financials -->
                <div class="mb-12">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Financials</h3>
                    <table class="w-full text-sm">
                        <thead class="bg-gray-900 text-white dark:bg-white dark:text-gray-900 uppercase text-[10px] tracking-widest">
                            <tr>
                                <th class="px-4 py-3 text-left">Description</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right">Rate</th>
                                <th class="px-4 py-3 text-right">Est. Cost</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-gray-800 border-b border-gray-100 dark:border-gray-800">
                            @if($manifest->costEstimates->count() > 0)
                                @foreach($manifest->costEstimates as $estimate)
                                    <tr>
                                        <td class="px-4 py-4 text-gray-900 dark:text-white font-bold">{{ $estimate->description ?: ucfirst($estimate->type) }}</td>
                                        <td class="px-4 py-4 text-right text-gray-600 dark:text-gray-400 font-medium">{{ $estimate->qty }}</td>
                                        <td class="px-4 py-4 text-right text-gray-600 dark:text-gray-400 font-medium">${{ number_format($estimate->rate, 2) }}</td>
                                        <td class="px-4 py-4 text-right text-gray-900 dark:text-white font-black">${{ number_format($estimate->est_cost, 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td class="px-4 py-8 text-center text-gray-400 italic" colspan="4 text-[10px]">No financials recorded</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="px-4 py-6 text-right font-black text-gray-500 uppercase tracking-widest text-[10px]">Total Agreed Rate</td>
                                <td class="px-4 py-6 text-right">
                                    <span class="text-2xl font-black text-primary-600">${{ number_format($manifest->costEstimates->sum('est_cost'), 2) }} CAD</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <!-- Terms & Conditions -->
                <div class="mb-12">
                    <h3 class="text-xs font-black text-gray-400 uppercase tracking-[0.2em] mb-4 border-b border-gray-100 dark:border-gray-800 pb-2">Terms and Conditions</h3>
                    <div class="text-[10px] text-gray-500 dark:text-gray-400 leading-relaxed uppercase font-medium space-y-3">
                        <p>BAD ORDER CONTAINERS MUST BE REPORTED TO INNOVATIONS LOGISTICS WITH PICTURE DOCUMENTATION AT THE TIME OF EMPTY PICKUP. ANY ACCESSORIAL CHARGES MUST BE REQUESTED WITH SUPPORTING DOCUMENTATION AT THE TIME OF DELIVERY. NON-COMPLIANCE WITH THIS POLICY WILL RESULT IN NO APPROVAL OF ACCESSORIAL CHARGES.</p>
                        <p>IF THERE ARE ANY DIFFERENCES BETWEEN THE INSTRUCTIONS ON THIS CONFIRMATION FROM WHAT YOU ARE PICKING UP, STOP AND THINK - CALL THE CONTACT ON THIS CONFIRMATION AND TELL THEM BEFORE YOU PICKUP</p>
                        <p>MAXIMUM LIABILITY FOR LOSS OR DAMAGE WILL NOT EXCEED $2.00 PER POUND AND ALL SHIPMENTS ARE SUBJECT TO A MAXIMUM LIABILITY OF $100,000.00 PER SHIPMENT, WHICHEVER IS LOWER.</p>
                        <p>ALL SHIPMENTS VALUED OVER $2.00 PER POUND MUST BE APPROVED AND ACCEPTED IN WRITING BY THE COMPANY MANAGEMENT BEFORE BEING PICKED UP BY THE CARRIER.</p>
                        <p>CARRIER MUST NOT RE-BROKER OR DOUBLE BROKER THIS LOAD WITHOUT THE EXPRESS WRITTEN PERMISSION AND AGREE THAT THEY WILL NOT BACK SOLICIT CUSTOMERS PRESENTED WITH THIS CONFIRMATION.</p>
                    </div>
                </div>

                <!-- Signature -->
                <div class="grid grid-cols-2 gap-24 pt-12 border-t-2 border-gray-900 dark:border-white">
                    <div>
                        <div class="h-12 border-b-2 border-gray-200 dark:border-gray-800 mb-2"></div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Authorized Signature</div>
                    </div>
                    <div>
                        <div class="h-12 border-b-2 border-gray-200 dark:border-gray-800 mb-2"></div>
                        <div class="text-[10px] font-black text-gray-400 uppercase tracking-widest">Carrier Acceptance (Name & Date)</div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
