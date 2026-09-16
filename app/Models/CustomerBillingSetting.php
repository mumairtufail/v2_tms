<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Accounting tab defaults for a customer. Saved now; used when invoices are sent.
 */
class CustomerBillingSetting extends Model
{
    public const TERMS = [
        'due_on_receipt' => 'Due on receipt',
        'net_7' => 'Net 7',
        'net_15' => 'Net 15',
        'net_30' => 'Net 30',
        'net_45' => 'Net 45',
        'net_60' => 'Net 60',
    ];

    public const SEND_METHODS = [
        'individual' => 'Email each invoice separately',
        'combined' => 'Email all invoices in one message',
        'manual' => "Don't email — download only",
    ];

    public const MAX_RECIPIENTS = 5;

    protected $fillable = [
        'customer_id',
        'invoice_terms',
        'taxable',
        'recipients',
        'attach_proof_of_pickup',
        'attach_proof_of_delivery',
        'attach_commercial_invoice',
        'combine_documents',
        'bulk_send_method',
    ];

    protected $casts = [
        'taxable' => 'boolean',
        'recipients' => 'array',
        'attach_proof_of_pickup' => 'boolean',
        'attach_proof_of_delivery' => 'boolean',
        'attach_commercial_invoice' => 'boolean',
        'combine_documents' => 'boolean',
    ];

    protected $attributes = [
        'bulk_send_method' => 'individual',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
