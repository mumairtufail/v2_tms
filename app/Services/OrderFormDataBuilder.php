<?php

namespace App\Services;

use App\Models\Accessorial;
use App\Models\Company;
use App\Models\Order;
use Carbon\Carbon;

class OrderFormDataBuilder
{
    public function build(Company $company, Order $order, bool $allowTypeOverride = true): array
    {
        $order->load([
            'stops.commodities',
            'stops.accessorials',
            'customer',
            'manifest.drivers',
            'manifest.carriers',
            'manifest.equipments',
            'quote.costs',
        ]);

        if ($allowTypeOverride && request()->has('type')) {
            $order->order_type = request('type');
        }

        $services = \App\Models\Service::all();
        $allAccessorials = $this->accessorialsFor($company, $order);
        $manifests = \App\Models\Manifest::where('company_id', $company->id)->get();

        $customerCommodities = $order->customer
            ? $order->customer->commodities()->orderBy('description')->get()->map->toOrderPreset()->values()
            : collect();
        $requireDimensions = (bool) $order->customer?->require_dimensions;

        $stopsData = $order->stops->map(fn ($stop) => $this->mapStop($stop));

        $quote = $order->quote ?? new \App\Models\OrderQuote();
        $quoteData = [
            'service_id' => $quote->service_id ?? '',
            'delivery_start' => $quote->delivery_start_date ? Carbon::parse($quote->delivery_start_date)->format('Y-m-d\\TH:i') : '',
            'delivery_end' => $quote->delivery_end_date ? Carbon::parse($quote->delivery_end_date)->format('Y-m-d\\TH:i') : '',
            'customer_rows' => $quote->costs->where('category', 'customer')->map(fn ($c) => [
                'type' => $c->type ?? 'Freight',
                'description' => $c->description ?? '',
                'qty' => $c->qty ?? 0,
                'rate' => $c->rate ?? 0,
                'cost' => $c->cost ?? 0,
                'percentage' => $c->percentage ?? null,
                'is_default' => false,
            ])->values()->toArray(),
            // Filled below from the manifest, or from the order while it has none
            'carrier_rows' => [],
        ];

        $carrierAllocation = app(ManifestService::class)->carrierCostForOrder($order);

        // Carrier cost lives on the manifest. It can be typed here too, but only when every
        // stop sits on the same manifest — otherwise there is no safe way to divide one figure.
        $stopManifestIds = $order->stops->pluck('manifest_id')->filter()->unique()->values();
        $carrierTargetManifest = $stopManifestIds->count() === 1
            ? \App\Models\Manifest::with('costEstimates')->find($stopManifestIds->first())
            : null;

        // Editable with one manifest (saved there) or none yet (saved on the order until
        // a manifest exists). Across several manifests it stays read-only.
        $carrierEditable = $stopManifestIds->count() <= 1;

        if ($carrierTargetManifest) {
            $quoteData['carrier_rows'] = $carrierTargetManifest->costEstimates->map(fn ($c) => [
                'type' => $c->type ?? 'Freight',
                'description' => $c->description ?? '',
                'qty' => $c->qty ?? 0,
                'rate' => $c->rate ?? 0,
                'cost' => $c->est_cost ?? 0,
                'percentage' => null,
                'is_default' => false,
            ])->values()->toArray();
        } elseif ($carrierEditable) {
            // No manifest yet: the estimate lives on the order
            $quoteData['carrier_rows'] = $quote->costs->where('category', 'carrier')->map(fn ($c) => [
                'type' => $c->type ?? 'Freight',
                'description' => $c->description ?? '',
                'qty' => $c->qty ?? 0,
                'rate' => $c->rate ?? 0,
                'cost' => $c->cost ?? 0,
                'percentage' => $c->percentage ?? null,
                'is_default' => false,
            ])->values()->toArray();
        }

        $manifestsMap = $manifests->pluck('code', 'id')->toArray();

        if (empty($quoteData['customer_rows'])) {
            $quoteData['customer_rows'][] = ['type' => 'Freight', 'description' => '', 'cost' => 0];
        }

        return compact('company', 'order', 'services', 'stopsData', 'allAccessorials', 'manifests', 'quoteData', 'manifestsMap', 'customerCommodities', 'requireDimensions', 'carrierAllocation', 'carrierEditable', 'carrierTargetManifest');
    }

    /**
     * The company's accessorials this order can use: the ones enabled for the
     * customer, plus any already on the order so existing selections never vanish.
     */
    protected function accessorialsFor(Company $company, Order $order)
    {
        $companyAccessorials = Accessorial::forCompany($company->id)->orderBy('name')->get();
        $attachedIds = $order->stops->flatMap(fn ($stop) => $stop->accessorials->pluck('id'))->unique();

        $allowedIds = $order->customer
            ? $order->customer->accessorials()->pluck('accessorials.id')
            : $companyAccessorials->pluck('id');

        return $companyAccessorials
            ->filter(fn (Accessorial $accessorial) => $attachedIds->contains($accessorial->id)
                || ($accessorial->is_active && $allowedIds->contains($accessorial->id)))
            ->values();
    }

    protected function mapStop($stop): array
    {
        $consignee = $stop->consignee_data ?? [];
        $billing = $stop->billing_data ?? [];

        if (empty($consignee['shipper_ready_start_at']) && !empty($stop->start_time)) {
            $consignee['shipper_ready_start_at'] = $this->formatOrderDateTime($stop->start_time);
        }
        if (empty($consignee['shipper_ready_end_at']) && !empty($stop->start_time)) {
            $consignee['shipper_ready_end_at'] = $this->formatOrderDateTime($stop->start_time);
        }
        if (empty($consignee['requested_end_at']) && !empty($stop->end_time)) {
            $consignee['requested_end_at'] = $this->formatOrderDateTime($stop->end_time);
        }
        if (empty($consignee['requested_start_at']) && !empty($consignee['requested_end_at'])) {
            $consignee['requested_start_at'] = $consignee['requested_end_at'];
        }
        if (empty($consignee['ready_at']) && !empty($consignee['requested_end_at'])) {
            $consignee['ready_at'] = $consignee['requested_end_at'];
        }

        $consigneeDefaults = [
            'company_name' => '', 'address_1' => '', 'address_2' => '', 'city' => '', 'state' => '', 'zip' => '', 'country' => 'US',
            'lat' => null, 'lng' => null,
            'contact_name' => '', 'phone' => '', 'email' => '', 'opening_time' => '08:00', 'closing_time' => '17:00',
            'ready_at' => '',
            'requested_start_at' => '',
            'requested_end_at' => '',
            'appointment' => false,
            'notes' => '',
        ];

        return [
            'uid' => uniqid(),
            'expanded' => false,
            'manifest_id' => $stop->manifest_id ? (string) $stop->manifest_id : '',
            'service_type' => $stop->service_type ?? 'truckload',
            'measurements' => $stop->measurement_type ?? 'in_lbs',
            'shipper' => [
                'company_name' => $stop->company_name ?? '',
                'address_1' => $stop->address_1 ?? '',
                'address_2' => $stop->address_2 ?? '',
                'city' => $stop->city ?? '',
                'state' => $stop->state ?? '',
                'zip' => $stop->postal_code ?? '',
                'country' => $stop->country ?? 'US',
                'lat' => $stop->lat,
                'lng' => $stop->lng,
                'contact_name' => $stop->contact_name ?? '',
                'phone' => $stop->contact_phone ?? '',
                'email' => $stop->contact_email ?? '',
                'opening_time' => $stop->opening_time ?? '08:00',
                'closing_time' => $stop->closing_time ?? '17:00',
                'ready_at' => $this->formatOrderDateTime($stop->start_time),
                'ready_start_at' => $consignee['shipper_ready_start_at'] ?? $this->formatOrderDateTime($stop->start_time),
                'ready_end_at' => $consignee['shipper_ready_end_at'] ?? $this->formatOrderDateTime($stop->start_time),
                'appointment' => (bool) $stop->is_appointment,
                'notes' => $stop->notes ?? '',
            ],
            'consignee' => array_merge($consigneeDefaults, $consignee),
            'billing' => array_merge([
                'customs_broker' => '',
                'port_of_entry' => '',
                'container_number' => '',
                'declared_value' => 0,
                'currency' => 'USD',
                'ref_number' => '',
                'customer_po_number' => '',
            ], $billing),
            'commodities' => $stop->commodities->count() > 0 ? $stop->commodities->map(fn ($c) => [
                'description' => $c->description,
                'qty' => $c->quantity,
                'type' => $c->type ?? 'skid',
                'weight' => $c->weight !== null ? (float) $c->weight : '',
                'length' => $c->length !== null ? (float) $c->length : '',
                'width' => $c->width !== null ? (float) $c->width : '',
                'height' => $c->height !== null ? (float) $c->height : '',
                'pcs' => $c->pieces !== null ? (int) $c->pieces : '',
                'lf' => $c->linear_feet !== null ? (float) $c->linear_feet : '',
                'cube' => $c->cube !== null ? (float) $c->cube : '',
                'freight_class' => $c->freight_class ?? '',
            ])->toArray() : [[
                'description' => '',
                'qty' => 1,
                'type' => 'skid',
                'weight' => '',
                'length' => '',
                'width' => '',
                'height' => '',
                'pcs' => '',
                'lf' => '',
                'cube' => '',
                'freight_class' => '',
            ]],
            'accessorials' => $stop->accessorials->pluck('id')->map(fn ($id) => (string) $id)->toArray(),
            'special_instructions' => $stop->special_instructions ?? '',
        ];
    }

    protected function formatOrderDateTime($value): string
    {
        if (empty($value)) {
            return '';
        }

        try {
            return Carbon::parse($value)->format('m/d/y H:i');
        } catch (\Throwable $e) {
            return '';
        }
    }
}
