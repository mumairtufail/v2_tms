{{-- Accounting tab --}}
@php
    use App\Models\CustomerBillingSetting;

    $recipients = old('recipients', $settings->recipients ?? []);
@endphp

<form method="POST" action="{{ route('v2.customers.billing-settings.update', ['company' => $company->slug, 'customer' => $customer->id]) }}"
      x-data="{ recipients: @js(array_values($recipients ?: [''])), max: {{ CustomerBillingSetting::MAX_RECIPIENTS }}, dirty: false }"
      @input="dirty = true" @change="dirty = true" class="space-y-6 max-w-3xl">
    @csrf
    @method('PUT')

    <div class="flex items-start gap-2.5 rounded-lg border border-amber-200 dark:border-amber-900/50 bg-amber-50 dark:bg-amber-900/10 px-3.5 py-2.5">
        <svg class="w-4 h-4 mt-0.5 shrink-0 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        <p class="text-sm text-amber-800 dark:text-amber-300">These defaults are saved now and applied to this customer's invoices once invoicing is turned on.</p>
    </div>

    <fieldset @disabled(! $canEdit) class="space-y-6">
        <section class="space-y-3">
            <div>
                <p class="{{ $sectionTitle }}">Invoice details</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Default invoice terms and tax settings for all new invoices for this customer.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-end">
                <div>
                    <label class="{{ $label }}" for="a-terms">Default invoice terms</label>
                    <select id="a-terms" name="invoice_terms" class="{{ $input }}">
                        <option value="">Select terms</option>
                        @foreach(CustomerBillingSetting::TERMS as $value => $name)
                        <option value="{{ $value }}" @selected(old('invoice_terms', $settings->invoice_terms) === $value)>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-toggle-switch name="taxable" label="Invoice items are taxable" :checked="(bool) old('taxable', $settings->taxable)" />
            </div>
        </section>

        <section class="space-y-3 pt-5 border-t border-gray-100 dark:border-gray-800">
            <div>
                <p class="{{ $sectionTitle }}">Invoice email settings</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Email addresses that fill the recipient field on every invoice sent to this customer.</p>
            </div>
            <div class="space-y-2">
                <template x-for="(email, index) in recipients" :key="index">
                    <div class="flex items-center gap-2">
                        <input type="email" name="recipients[]" x-model="recipients[index]" placeholder="Email address"
                               class="flex-1 py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md placeholder:text-gray-400 focus:border-primary-500 focus:ring-primary-500">
                        <button type="button" @click="recipients.splice(index, 1); if (!recipients.length) recipients.push(''); dirty = true" class="p-1.5 text-gray-400 hover:text-red-600" aria-label="Remove recipient">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>
                </template>
                <button type="button" x-show="recipients.length < max" @click="recipients.push('')" class="text-xs font-semibold text-primary-600 hover:underline">+ Add email address</button>
                @error('recipients')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                @error('recipients.*')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            @if($invoiceContacts->isNotEmpty())
            <p class="text-xs text-gray-500 dark:text-gray-400">
                Also sent to people set to receive invoices on the People tab:
                {{ $invoiceContacts->map(fn ($c) => $c->name . ' (' . $c->email . ($c->cc_on_invoices && ! $c->send_invoices ? ', CC' : '') . ')')->join(', ') }}
            </p>
            @endif
        </section>

        <section class="space-y-3 pt-5 border-t border-gray-100 dark:border-gray-800">
            <div>
                <p class="{{ $sectionTitle }}">Attached documents</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Documents attached to every invoice. A warning appears when a document hasn't been uploaded yet.</p>
            </div>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
                @foreach(['attach_proof_of_pickup' => 'Proof of pickup', 'attach_proof_of_delivery' => 'Proof of delivery', 'attach_commercial_invoice' => 'Commercial invoice'] as $field => $name)
                <label class="inline-flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="{{ $field }}" value="1" @checked(old($field, $settings->{$field})) class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700 dark:text-gray-300">{{ $name }}</span>
                </label>
                @endforeach
            </div>
            <x-toggle-switch name="combine_documents" label="Combine all documents into a single PDF per invoice" :checked="(bool) old('combine_documents', $settings->combine_documents)" />
        </section>

        <section class="space-y-3 pt-5 border-t border-gray-100 dark:border-gray-800">
            <div>
                <p class="{{ $sectionTitle }}">Bulk send settings</p>
                <p class="text-xs text-gray-500 dark:text-gray-400">Defaults when sending several invoices to this customer at once.</p>
            </div>
            <div class="max-w-sm">
                <label class="{{ $label }}" for="a-send">Send method</label>
                <select id="a-send" name="bulk_send_method" class="{{ $input }}">
                    @foreach(CustomerBillingSetting::SEND_METHODS as $value => $name)
                    <option value="{{ $value }}" @selected(old('bulk_send_method', $settings->bulk_send_method ?? 'individual') === $value)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
        </section>
    </fieldset>

    @if($canEdit)
    <div class="flex justify-end">
        <button type="submit" class="{{ $primaryButton }}" :class="dirty ? '' : 'opacity-60'">Save invoice settings</button>
    </div>
    @endif
</form>
