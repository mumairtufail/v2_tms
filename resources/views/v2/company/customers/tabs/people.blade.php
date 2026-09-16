{{-- People tab --}}
@php
    use App\Enums\CustomerNotificationEvent;
    use App\Models\CustomerContactPhone;

    $contactsJson = $contacts->map(fn ($c) => [
        'id' => $c->id,
        'first_name' => $c->first_name,
        'last_name' => $c->last_name,
        'job_title' => $c->job_title,
        'email' => $c->email,
        'notes' => $c->notes,
        'send_invoices' => $c->send_invoices,
        'portal_access' => $c->portal_access,
        'cc_on_invoices' => $c->cc_on_invoices,
        'has_password' => filled($c->password),
        'phones' => $c->phones->map(fn ($p) => ['type' => $p->type, 'number' => $p->number, 'ext' => $p->ext])->values(),
        'email_prefs' => collect($c->preferences())->map(fn ($p) => (bool) $p['email']),
    ])->keyBy('id');

    $oldPerson = null;
    if (old('_form') === 'person') {
        $oldPerson = [
            'id' => old('_contact_id') ?: null,
            'first_name' => old('first_name'),
            'last_name' => old('last_name'),
            'job_title' => old('job_title'),
            'email' => old('email'),
            'notes' => old('notes'),
            'send_invoices' => (bool) old('send_invoices'),
            'portal_access' => (bool) old('portal_access'),
            'cc_on_invoices' => (bool) old('cc_on_invoices'),
            'has_password' => (bool) ($contactsJson[old('_contact_id')]['has_password'] ?? false),
            'phones' => array_values((array) old('phones', [])),
            'email_prefs' => collect((array) old('email_notifications', []))->map(fn ($v) => (bool) $v),
        ];
    }
@endphp

<div x-data="peopleManager({
        contacts: @js($contactsJson),
        storeUrl: '{{ route('v2.customers.contacts.store', ['company' => $company->slug, 'customer' => $customer->id]) }}',
        updateUrl: '{{ route('v2.customers.contacts.update', ['company' => $company->slug, 'customer' => $customer->id, 'contact' => '__ID__']) }}',
        old: @js($oldPerson),
    })">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $contacts->count() === 1 ? '1 person' : $contacts->count() . ' people' }}</p>
        @if($canEdit)
        <button type="button" @click="openPerson(null)" class="{{ $primaryButton }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add person
        </button>
        @endif
    </div>

    @if($contacts->isEmpty())
    <div class="rounded-lg border border-dashed border-gray-300 dark:border-gray-700 px-6 py-10 text-center">
        <p class="text-sm font-medium text-gray-900 dark:text-white">No people yet</p>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Add the people who book orders, receive invoices or sign in to the portal.</p>
    </div>
    @else
    {{-- contain: keeps a wide table scrolling inside this box instead of widening the page on phones --}}
    <div class="overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800 [contain:inline-size_layout_paint]">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 dark:bg-gray-800/50">
                <tr class="text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Email</th>
                    <th class="px-4 py-2">Contact number</th>
                    <th class="px-4 py-2">Access</th>
                    <th class="px-4 py-2 w-24"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($contacts as $contact)
                <tr class="align-top">
                    <td class="px-4 py-2.5">
                        <div class="flex items-center gap-2.5">
                            <span class="w-7 h-7 rounded-full shrink-0 grid place-items-center bg-gray-100 dark:bg-gray-800 text-[10px] font-bold text-gray-600 dark:text-gray-300">{{ $contact->initials() }}</span>
                            <div class="min-w-0">
                                <p class="font-medium text-gray-900 dark:text-white">{{ $contact->name }}</p>
                                @if($contact->job_title)<p class="text-xs text-gray-500">{{ $contact->job_title }}</p>@endif
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300">{{ $contact->email ?: '—' }}</td>
                    <td class="px-4 py-2.5 text-gray-700 dark:text-gray-300 whitespace-nowrap">
                        @forelse($contact->phones as $phone)
                        <p><span class="text-[11px] text-gray-400">{{ CustomerContactPhone::TYPES[$phone->type] ?? 'Phone' }}</span> {{ $phone->display() }}</p>
                        @empty
                        —
                        @endforelse
                    </td>
                    <td class="px-4 py-2.5">
                        <div class="flex flex-wrap gap-1">
                            @if($contact->portal_access)<span class="px-1.5 py-0.5 rounded bg-primary-50 dark:bg-primary-900/30 text-[11px] font-medium text-primary-700 dark:text-primary-300">Portal</span>@endif
                            @if($contact->send_invoices)<span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[11px] font-medium text-gray-600 dark:text-gray-300">Invoices</span>@endif
                            @if($contact->cc_on_invoices)<span class="px-1.5 py-0.5 rounded bg-gray-100 dark:bg-gray-800 text-[11px] font-medium text-gray-600 dark:text-gray-300">CC invoices</span>@endif
                            @unless($contact->portal_access || $contact->send_invoices || $contact->cc_on_invoices)<span class="text-xs text-gray-400">Contact only</span>@endunless
                        </div>
                    </td>
                    <td class="px-4 py-2.5 text-right whitespace-nowrap">
                        @if($canEdit)
                        <button type="button" @click="openPerson({{ $contact->id }})" class="text-xs font-medium text-primary-600 hover:underline">Edit</button>
                        <button type="button" @click="askDelete('{{ route('v2.customers.contacts.destroy', ['company' => $company->slug, 'customer' => $customer->id, 'contact' => $contact->id]) }}', @js($contact->name), @js((bool) $contact->portal_access))"
                                class="ml-3 text-xs font-medium text-gray-400 hover:text-red-600">Remove</button>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    @if($canEdit)
    @include('v2.company.customers.partials.confirm-delete-modal', ['name' => 'confirm-person-delete'])

    <x-modal name="person-modal" maxWidth="3xl" focusable>
        <form method="POST" :action="action">
            @csrf
            <template x-if="form.id"><input type="hidden" name="_method" value="PUT"></template>
            <input type="hidden" name="_form" value="person">
            <input type="hidden" name="_contact_id" :value="form.id || ''">

            <div class="px-6 pt-5 pb-4 border-b border-gray-100 dark:border-gray-800 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white" x-text="form.id ? 'Edit person' : 'Add person'"></h2>
                <button type="button" @click="$dispatch('close-modal', 'person-modal')" class="p-1 text-gray-400 hover:text-gray-600 rounded" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="px-6 py-5 space-y-5 max-h-[70vh] overflow-y-auto">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                    <div>
                        <label class="{{ $label }}">First name <span class="text-red-500">*</span></label>
                        <input type="text" name="first_name" x-model="form.first_name" required class="{{ $input }}">
                        @error('first_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Last name</label>
                        <input type="text" name="last_name" x-model="form.last_name" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Job</label>
                        <input type="text" name="job_title" x-model="form.job_title" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Email</label>
                        <input type="email" name="email" x-model="form.email" class="{{ $input }}">
                        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="{{ $label }}">Notes</label>
                        <textarea name="notes" x-model="form.notes" rows="2" class="{{ $input }} resize-none"></textarea>
                    </div>
                </div>

                {{-- Contact numbers --}}
                <div>
                    <p class="{{ $label }}">Contact number</p>
                    <div class="mt-1 space-y-2">
                        <template x-for="(phone, index) in form.phones" :key="index">
                            <div class="flex items-center gap-2">
                                <select :name="`phones[${index}][type]`" x-model="phone.type" class="w-28 py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md focus:border-primary-500 focus:ring-primary-500">
                                    @foreach(CustomerContactPhone::TYPES as $value => $name)
                                    <option value="{{ $value }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                                <input type="tel" :name="`phones[${index}][number]`" x-model="phone.number" placeholder="Number" class="flex-1 min-w-0 py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md focus:border-primary-500 focus:ring-primary-500">
                                <input type="text" :name="`phones[${index}][ext]`" x-model="phone.ext" placeholder="Ext" class="w-20 py-1.5 text-sm border-gray-200 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200 rounded-md focus:border-primary-500 focus:ring-primary-500">
                                <button type="button" @click="form.phones.splice(index, 1)" class="p-1.5 text-gray-400 hover:text-red-600" aria-label="Remove number">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </template>
                        <button type="button" @click="addPhone()" x-show="form.phones.length < 5" class="text-xs font-semibold text-primary-600 hover:underline">+ Add contact number</button>
                        @error('phones.*')<p class="text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                {{-- Access --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4 border-t border-gray-100 dark:border-gray-800">
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="send_invoices" value="1" x-model="form.send_invoices" class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span><span class="block text-sm font-medium text-gray-900 dark:text-white">Send invoices</span><span class="text-xs text-gray-500">Added to invoice recipients.</span></span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer">
                        <input type="checkbox" name="cc_on_invoices" value="1" x-model="form.cc_on_invoices" class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span><span class="block text-sm font-medium text-gray-900 dark:text-white">CC me on invoices</span><span class="text-xs text-gray-500">Copied on invoices sent to others.</span></span>
                    </label>
                    <label class="flex items-start gap-2.5 opacity-60" title="Coming soon">
                        <input type="checkbox" disabled class="mt-0.5 rounded border-gray-300">
                        <span><span class="block text-sm font-medium text-gray-900 dark:text-white">Send reports</span><span class="text-xs text-gray-500">Coming soon</span></span>
                    </label>
                    <label class="flex items-start gap-2.5 opacity-60" title="Coming soon">
                        <input type="checkbox" disabled class="mt-0.5 rounded border-gray-300">
                        <span><span class="block text-sm font-medium text-gray-900 dark:text-white">Send dispatch notifications</span><span class="text-xs text-gray-500">Coming soon</span></span>
                    </label>
                    <label class="flex items-start gap-2.5 cursor-pointer sm:col-span-2">
                        <input type="checkbox" name="portal_access" value="1" x-model="form.portal_access" class="mt-0.5 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                        <span><span class="block text-sm font-medium text-gray-900 dark:text-white">Portal access</span><span class="text-xs text-gray-500">Signs in at {{ route('portal.login', ['company' => $company->slug]) }} with their email.</span></span>
                    </label>
                </div>

                <div x-show="form.portal_access" x-cloak class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3 rounded-lg bg-gray-50 dark:bg-gray-800/50 p-3">
                    <div>
                        <label class="{{ $label }}">Portal password</label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" name="password" x-model="password" autocomplete="new-password"
                                   :placeholder="form.has_password ? 'Leave blank to keep current password' : 'At least 8 characters'" class="{{ $input }} pr-16">
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 right-2 flex items-center text-[11px] font-medium text-gray-500 hover:text-gray-800" x-text="showPassword ? 'Hide' : 'Show'"></button>
                        </div>
                        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="{{ $label }}">Confirm password</label>
                        <input :type="showPassword ? 'text' : 'password'" name="password_confirmation" x-model="passwordConfirmation" autocomplete="new-password" class="{{ $input }}">
                    </div>
                    <p class="sm:col-span-2 text-[11px] text-gray-500">Use upper and lower case letters, a number and a symbol. <button type="button" @click="generatePassword()" class="font-medium text-primary-600 hover:underline">Generate one</button></p>
                </div>

                {{-- Notifications --}}
                <div class="pt-4 border-t border-gray-100 dark:border-gray-800">
                    <p class="{{ $sectionTitle }}">Notifications</p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">In-app notifications appear in the portal for people with portal access.</p>
                    <div class="mt-2 overflow-x-auto rounded-lg border border-gray-200 dark:border-gray-800">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 dark:bg-gray-800/50">
                                <tr class="text-left text-[11px] font-semibold text-gray-500 dark:text-gray-400">
                                    <th class="px-3 py-1.5">Notification</th>
                                    <th class="px-3 py-1.5 w-20 text-center">In-app</th>
                                    <th class="px-3 py-1.5 w-20 text-center">Email</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                                @foreach(CustomerNotificationEvent::cases() as $event)
                                <tr>
                                    <td class="px-3 py-1.5 text-gray-700 dark:text-gray-300">
                                        {{ $event->label() }}
                                        @unless($event->isAvailable())<span class="ml-1 text-[10px] text-gray-400">Coming soon</span>@endunless
                                    </td>
                                    <td class="px-3 py-1.5 text-center"><input type="checkbox" checked disabled aria-label="In-app, always on" class="rounded border-gray-300 text-gray-400"></td>
                                    <td class="px-3 py-1.5 text-center">
                                        @if($event->isAvailable())
                                        <input type="checkbox" name="email_notifications[{{ $event->value }}]" value="1" x-model="form.email_prefs['{{ $event->value }}']" aria-label="Email for {{ $event->label() }}" class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                                        @else
                                        <input type="checkbox" disabled aria-label="Email for {{ $event->label() }}, coming soon" class="rounded border-gray-200">
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="px-6 py-3.5 border-t border-gray-100 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/60 flex justify-end gap-2 rounded-b-lg">
                <button type="button" @click="$dispatch('close-modal', 'person-modal')" class="{{ $secondaryButton }}">Cancel</button>
                <button type="submit" class="{{ $primaryButton }}" x-text="form.id ? 'Save' : 'Add person'"></button>
            </div>
        </form>
    </x-modal>
    @endif
</div>

@push('scripts')
<script>
function peopleManager(config) {
    const blank = () => ({
        id: null, first_name: '', last_name: '', job_title: '', email: '', notes: '',
        send_invoices: false, portal_access: false, cc_on_invoices: false, has_password: false,
        phones: [], email_prefs: {},
    });

    return {
        form: blank(),
        password: '',
        passwordConfirmation: '',
        showPassword: false,
        confirmAction: '',
        confirmTitle: '',
        confirmBody: '',
        confirmIds: [],

        askDelete(action, name, hasPortalAccess) {
            this.confirmAction = action;
            this.confirmTitle = 'Remove person';
            this.confirmBody = `Remove ${name}?` + (hasPortalAccess ? ' They will no longer be able to sign in to the portal.' : '');
            this.$dispatch('open-modal', 'confirm-person-delete');
        },

        init() {
            if (config.old) {
                this.form = Object.assign(blank(), config.old);
                this.$nextTick(() => this.$dispatch('open-modal', 'person-modal'));
            }
        },

        get action() {
            return this.form.id ? config.updateUrl.replace('__ID__', this.form.id) : config.storeUrl;
        },

        openPerson(id) {
            const contact = id ? config.contacts[id] : null;
            this.form = contact ? JSON.parse(JSON.stringify(contact)) : blank();
            this.password = '';
            this.passwordConfirmation = '';
            this.showPassword = false;
            this.$dispatch('open-modal', 'person-modal');
        },

        addPhone() {
            if (this.form.phones.length < 5) this.form.phones.push({ type: 'office', number: '', ext: '' });
        },

        generatePassword() {
            const sets = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnpqrstuvwxyz', '23456789', '!@#$%&*?'];
            const pick = (chars) => chars[crypto.getRandomValues(new Uint32Array(1))[0] % chars.length];
            let value = sets.map(pick).join('');
            while (value.length < 14) value += pick(sets.join(''));
            this.password = this.passwordConfirmation = value.split('').sort(() => Math.random() - 0.5).join('');
            this.showPassword = true;
        },
    };
}
</script>
@endpush
