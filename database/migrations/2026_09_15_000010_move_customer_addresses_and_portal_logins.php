<?php

use App\Enums\CustomerNotificationEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Data migration for the customers revamp:
 *  - each customer's single address becomes its billing address,
 *  - each portal login (customer_email + password) becomes a person, keeping
 *    the existing password hash so nobody has to reset,
 *  - existing in-app notifications follow that person,
 *  - every customer can use all of its company's accessorials.
 *
 * The legacy customers columns stay in place for now.
 */
return new class extends Migration
{
    private const CUSTOMER_MODEL = 'App\\Models\\Customer';
    private const CONTACT_MODEL = 'App\\Models\\CustomerContact';

    public function up(): void
    {
        $now = now();
        $defaultPrefs = json_encode(CustomerNotificationEvent::defaults());

        $accessorialIdsByCompany = DB::table('accessorials')
            ->select('id', 'company_id')
            ->get()
            ->groupBy('company_id')
            ->map(fn ($rows) => $rows->pluck('id')->all());

        DB::table('customers')->orderBy('id')->chunkById(200, function ($customers) use ($now, $defaultPrefs, $accessorialIdsByCompany) {
            foreach ($customers as $customer) {
                if (!$customer->company_id) {
                    continue;
                }

                $hasAddress = filled($customer->address) || filled($customer->city);
                $alreadyHasBilling = DB::table('customer_addresses')->where('customer_id', $customer->id)->exists();

                if ($hasAddress && !$alreadyHasBilling) {
                    DB::table('customer_addresses')->insert([
                        'company_id' => $customer->company_id,
                        'customer_id' => $customer->id,
                        'company_name' => $customer->name,
                        'address_1' => (string) ($customer->address ?? ''),
                        'city' => (string) ($customer->city ?? ''),
                        'state' => $customer->state,
                        'postal_code' => $customer->postal_code,
                        'country' => $customer->country ? mb_substr($customer->country, 0, 10) : null,
                        'is_billing' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                if (filled($customer->customer_email)
                    && !DB::table('customer_contacts')->where('customer_id', $customer->id)->where('email', $customer->customer_email)->exists()) {
                    $contactId = DB::table('customer_contacts')->insertGetId([
                        'company_id' => $customer->company_id,
                        'customer_id' => $customer->id,
                        'first_name' => $customer->name,
                        'email' => $customer->customer_email,
                        'password' => $customer->password,
                        'portal_access' => (bool) $customer->portal,
                        'send_invoices' => true,
                        'notification_prefs' => $defaultPrefs,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    DB::table('notifications')
                        ->where('notifiable_type', self::CUSTOMER_MODEL)
                        ->where('notifiable_id', $customer->id)
                        ->update(['notifiable_type' => self::CONTACT_MODEL, 'notifiable_id' => $contactId]);
                }

                $companyAccessorials = $accessorialIdsByCompany->get($customer->company_id, []);
                if ($companyAccessorials && !DB::table('customer_accessorial')->where('customer_id', $customer->id)->exists()) {
                    DB::table('customer_accessorial')->insert(array_map(fn ($id) => [
                        'customer_id' => $customer->id,
                        'accessorial_id' => $id,
                    ], $companyAccessorials));
                }
            }
        });
    }

    public function down(): void
    {
        // Point notifications back at the customer; the copied rows are removed
        // when the earlier migrations drop their tables.
        DB::table('customer_contacts')->select('id', 'customer_id')->orderBy('id')->chunkById(200, function ($contacts) {
            foreach ($contacts as $contact) {
                DB::table('notifications')
                    ->where('notifiable_type', self::CONTACT_MODEL)
                    ->where('notifiable_id', $contact->id)
                    ->update(['notifiable_type' => self::CUSTOMER_MODEL, 'notifiable_id' => $contact->customer_id]);
            }
        });
    }
};
