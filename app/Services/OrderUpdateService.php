<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderUpdateService
{
    public function update(Request $request, Company $company, Order $order, array $options = []): RedirectResponse
    {
        $portal = (bool) ($options['portal'] ?? false);
        $forceOrderType = $options['force_order_type'] ?? null;
        $redirectRoute = $options['redirect_route'] ?? 'v2.orders.edit';

        $ordersLog = \Log::channel('orders');
        $ordersLog->info('=== Order Update Started ===', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => Auth::id(),
            'customer_id' => Auth::guard('customer')->id(),
            'portal' => $portal,
            'order_type' => $request->input('order_type'),
        ]);

        try {
            $stopsData = json_decode($request->input('stops', '[]'), true);
            $quoteData = json_decode($request->input('quote_data', '{}'), true);
            $saveAsDraft = $request->input('save_as_draft') === '1';
            $submissionMode = $request->input('submission_mode', 'new');

            $ordersLog->info('Parsed form data', [
                'stops_count' => count($stopsData ?? []),
                'has_quote_data' => !empty($quoteData),
                'save_as_draft' => $saveAsDraft,
                'submission_mode' => $submissionMode,
            ]);

            $validated = $request->validate([
                'order_type' => 'required|string',
                'ref_number' => 'nullable|string|max:255',
                'customer_po_number' => 'nullable|string|max:255',
                'container_number' => 'nullable|string|max:255',
                'special_instructions' => 'nullable|string',
            ]);

            if ($forceOrderType) {
                $validated['order_type'] = $forceOrderType;
            }

            $topContainerNumber = trim((string) ($validated['container_number'] ?? ''));
            if (!empty($topContainerNumber) && is_array($stopsData)) {
                foreach ($stopsData as &$stopData) {
                    if (!isset($stopData['billing']) || !is_array($stopData['billing'])) {
                        $stopData['billing'] = [];
                    }
                    $stopData['billing']['container_number'] = $topContainerNumber;
                }
                unset($stopData);
            }

            if ($portal) {
                $nextStatus = $saveAsDraft ? 'draft' : 'new';
            } else {
                $quoteHasContent = $this->quoteHasContent($quoteData);
                $nextStatus = 'new';
                if ($saveAsDraft) {
                    $nextStatus = 'draft';
                } elseif ($submissionMode === 'quote') {
                    $nextStatus = $quoteHasContent ? 'quoted' : 'no_quote';
                }
            }

            $order->update([
                'order_type' => $validated['order_type'] ?? $order->order_type,
                'ref_number' => $validated['ref_number'] ?? $order->ref_number,
                'customer_po_number' => $validated['customer_po_number'] ?? $order->customer_po_number,
                'container_number' => $validated['container_number'] ?? $order->container_number,
                'special_instructions' => $validated['special_instructions'] ?? $order->special_instructions,
                'status' => $nextStatus,
            ]);

            $ordersLog->info('Updated order basic fields', [
                'status' => $order->status,
                'ref_number' => $order->ref_number,
            ]);

            if (!empty($stopsData)) {
                $this->processStops($order, $stopsData, $ordersLog, $portal);
            }

            if (!$portal && !empty($quoteData)) {
                $this->processQuote($order, $quoteData, $ordersLog);
            }

            if (!$portal) {
                $this->saveContactBookEntries($request, $company, $ordersLog);
            }

            $ordersLog->info('=== Order Update Completed Successfully ===', [
                'order_id' => $order->id,
            ]);

            $message = $saveAsDraft ? 'Order saved as draft.' : 'Order submitted successfully.';

            if ($portal && !$saveAsDraft) {
                return redirect()
                    ->route('portal.orders.show', ['company' => $company->slug, 'order' => $order->id])
                    ->with('success', $message);
            }

            return redirect()
                ->route($redirectRoute, ['company' => $company->slug, 'order' => $order->id])
                ->with('success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            $ordersLog->warning('Order update validation failed', [
                'order_id' => $order->id,
                'errors' => $e->errors(),
            ]);

            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            $ordersLog->error('Order update failed', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Failed to update order: ' . $e->getMessage())->withInput();
        }
    }

    protected function saveContactBookEntries(Request $request, Company $company, $log): void
    {
        $entries = json_decode($request->input('contact_book_entries', '[]'), true);

        if (!is_array($entries) || count($entries) === 0) {
            return;
        }

        try {
            $saved = app(ContactBookService::class)->saveEntries($company->id, $entries);
            $log->info('Contact book entries saved', ['count' => $saved]);
        } catch (\Throwable $e) {
            $log->warning('Contact book save failed (order save unaffected)', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function processStops(Order $order, array $stopsData, $log, bool $portal = false): void
    {
        $log->info('Processing stops', ['count' => count($stopsData)]);

        $existingStopIds = $order->stops->pluck('id')->toArray();
        $processedStopIds = [];

        foreach ($stopsData as $index => $stopData) {
            $stopId = $stopData['id'] ?? null;

            $shipperReadyStartRaw = $stopData['shipper']['ready_start_at']
                ?? $stopData['shipper']['ready_start_at_picker']
                ?? $stopData['shipper']['ready_at']
                ?? null;
            $shipperReadyEndRaw = $stopData['shipper']['ready_end_at']
                ?? $stopData['shipper']['ready_end_at_picker']
                ?? $shipperReadyStartRaw;

            $consigneeRequestedStartRaw = $stopData['consignee']['requested_start_at']
                ?? $stopData['consignee']['requested_start_at_picker']
                ?? null;
            $consigneeRequestedEndRaw = $stopData['consignee']['requested_end_at']
                ?? $stopData['consignee']['requested_end_at_picker']
                ?? $stopData['consignee']['ready_at']
                ?? $consigneeRequestedStartRaw;

            $startTime = $this->parseOrderDateTime($shipperReadyStartRaw);
            if ($startTime === null) {
                $startTime = $this->parseOrderDateTime($stopData['shipper']['ready_at'] ?? null);
            }
            if ($startTime === null && !empty($stopData['shipper']['ready_date'])) {
                $legacyTime = !empty($stopData['shipper']['ready_time']) ? $stopData['shipper']['ready_time'] : '00:00';
                $startTime = $this->parseOrderDateTime($stopData['shipper']['ready_date'] . ' ' . $legacyTime);
            }

            $endTime = $this->parseOrderDateTime($consigneeRequestedEndRaw);
            if ($endTime === null) {
                $endTime = $this->parseOrderDateTime($stopData['consignee']['ready_at'] ?? null);
            }
            if ($endTime === null && !empty($stopData['consignee']['ready_date'])) {
                $legacyTime = !empty($stopData['consignee']['ready_time']) ? $stopData['consignee']['ready_time'] : '23:59';
                $endTime = $this->parseOrderDateTime($stopData['consignee']['ready_date'] . ' ' . $legacyTime);
            }

            $consigneePayload = $stopData['consignee'] ?? [];
            $consigneePayload['shipper_ready_start_at'] = $startTime ? $this->formatOrderDateTime($startTime) : ($consigneePayload['shipper_ready_start_at'] ?? '');
            $consigneePayload['shipper_ready_end_at'] = ($this->parseOrderDateTime($shipperReadyEndRaw) ? $this->formatOrderDateTime($this->parseOrderDateTime($shipperReadyEndRaw)) : ($consigneePayload['shipper_ready_end_at'] ?? ''));
            $consigneePayload['requested_start_at'] = ($this->parseOrderDateTime($consigneeRequestedStartRaw) ? $this->formatOrderDateTime($this->parseOrderDateTime($consigneeRequestedStartRaw)) : ($consigneePayload['requested_start_at'] ?? ''));
            $consigneePayload['requested_end_at'] = $endTime ? $this->formatOrderDateTime($endTime) : ($consigneePayload['requested_end_at'] ?? '');
            $consigneePayload['lat'] = $this->sanitizeCoordinate($consigneePayload['lat'] ?? null);
            $consigneePayload['lng'] = $this->sanitizeCoordinate($consigneePayload['lng'] ?? null);

            $manifestId = $portal ? null : (!empty($stopData['manifest_id']) ? $stopData['manifest_id'] : null);

            $stopFields = [
                'order_id' => $order->id,
                'stop_type' => 'mixed',
                'sequence_number' => $index + 1,
                'service_type' => $stopData['service_type'] ?? 'truckload',
                'measurement_type' => $stopData['measurements'] ?? 'in_lbs',
                'manifest_id' => $manifestId,
                'company_name' => $stopData['shipper']['company_name'] ?? '',
                'address_1' => $stopData['shipper']['address_1'] ?? '',
                'address_2' => $stopData['shipper']['address_2'] ?? '',
                'city' => $stopData['shipper']['city'] ?? '',
                'state' => $stopData['shipper']['state'] ?? '',
                'postal_code' => $stopData['shipper']['zip'] ?? '',
                'country' => $stopData['shipper']['country'] ?? 'US',
                'lat' => $this->sanitizeCoordinate($stopData['shipper']['lat'] ?? null),
                'lng' => $this->sanitizeCoordinate($stopData['shipper']['lng'] ?? null),
                'contact_name' => $stopData['shipper']['contact_name'] ?? '',
                'contact_phone' => $stopData['shipper']['phone'] ?? '',
                'contact_email' => $stopData['shipper']['email'] ?? '',
                'opening_time' => $stopData['shipper']['opening_time'] ?? null,
                'closing_time' => $stopData['shipper']['closing_time'] ?? null,
                'start_time' => $startTime,
                'end_time' => $endTime,
                'is_appointment' => (bool) ($stopData['shipper']['appointment'] ?? false),
                'notes' => $stopData['shipper']['notes'] ?? '',
                'special_instructions' => $stopData['special_instructions'] ?? '',
                'consignee_data' => $consigneePayload,
                'billing_data' => $stopData['billing'] ?? [],
            ];

            if ($stopId && in_array($stopId, $existingStopIds)) {
                $stop = \App\Models\OrderStop::find($stopId);
                $stop->update($stopFields);
                $processedStopIds[] = $stopId;
                $log->info('Updated stop', ['stop_id' => $stopId, 'sequence' => $index + 1]);
            } else {
                $stop = \App\Models\OrderStop::create($stopFields);
                $processedStopIds[] = $stop->id;
                $log->info('Created stop', ['stop_id' => $stop->id, 'sequence' => $index + 1]);
            }

            $this->processCommodities($stop, $stopData['commodities'] ?? [], $log, $stopData['measurements'] ?? 'imperial');

            $accessorialIds = array_map('intval', $stopData['accessorials'] ?? []);
            $stop->accessorials()->sync($accessorialIds);
            $log->info('Synced accessorials', ['stop_id' => $stop->id, 'count' => count($accessorialIds)]);
        }

        $stopsToDelete = array_diff($existingStopIds, $processedStopIds);
        if (!empty($stopsToDelete)) {
            \App\Models\OrderStop::whereIn('id', $stopsToDelete)->delete();
            $log->info('Deleted stops', ['count' => count($stopsToDelete)]);
        }
    }

    protected function processCommodities(\App\Models\OrderStop $stop, array $commoditiesData, $log, string $measurementType = 'imperial'): void
    {
        $stop->commodities()->delete();

        foreach ($commoditiesData as $comData) {
            $blankToNull = fn ($value) => ($value === '' || $value === null) ? null : $value;

            \App\Models\OrderStopCommodity::create([
                'order_stop_id' => $stop->id,
                'description' => $comData['description'] ?? '',
                'type' => $comData['type'] ?? 'skid',
                'quantity' => $comData['qty'] ?? 1,
                'pieces' => $blankToNull($comData['pcs'] ?? null) ?? 0,
                'weight' => $blankToNull($comData['weight'] ?? null) ?? 0,
                'length' => $blankToNull($comData['length'] ?? null),
                'width' => $blankToNull($comData['width'] ?? null),
                'height' => $blankToNull($comData['height'] ?? null),
                'linear_feet' => $blankToNull($comData['lf'] ?? null),
                'cube' => $blankToNull($comData['cube'] ?? null),
                'freight_class' => $comData['freight_class'] ?? null,
                'measurement_type' => $measurementType,
            ]);
        }

        $log->info('Recreated commodities', ['stop_id' => $stop->id, 'count' => count($commoditiesData)]);
    }

    protected function processQuote(Order $order, array $quoteData, $log): void
    {
        $quote = $order->quote ?? new \App\Models\OrderQuote(['order_id' => $order->id]);

        $quote->fill([
            'service_id' => $quoteData['service_id'] ?: null,
            'delivery_start_date' => $this->parseQuoteDate($quoteData['delivery_start'] ?? null),
            'delivery_end_date' => $this->parseQuoteDate($quoteData['delivery_end'] ?? null),
        ]);
        $quote->save();

        $quote->costs()->delete();

        $calcFreightSubtotal = function (array $rows): float {
            return collect($rows)->reduce(function ($carry, $row) {
                $t = strtolower($row['type'] ?? '');
                if ($t === 'freight' || $t === 'freight (per mile)') {
                    return $carry + ((float) ($row['qty'] ?? 0)) * ((float) ($row['rate'] ?? 0));
                }

                return $carry;
            }, 0.0);
        };

        $customerFreightBase = $calcFreightSubtotal($quoteData['customer_rows'] ?? []);
        $carrierFreightBase = $calcFreightSubtotal($quoteData['carrier_rows'] ?? []);

        foreach ($quoteData['customer_rows'] ?? [] as $row) {
            $typeStr = strtolower($row['type'] ?? '');
            $isSurcharge = ($typeStr === 'fuel (surcharge)');
            $qty = (float) ($row['qty'] ?? 0);
            $rate = (float) ($row['rate'] ?? 0);

            $cost = $isSurcharge
                ? round($customerFreightBase * ($qty / 100), 2)
                : round($qty * $rate, 2);

            $quote->costs()->create([
                'category' => 'customer',
                'type' => $row['type'] ?? 'Freight',
                'description' => $row['description'] ?? '',
                'qty' => $qty,
                'rate' => $isSurcharge ? $customerFreightBase : $rate,
                'cost' => $cost,
                'percentage' => $isSurcharge ? $qty : null,
            ]);
        }

        foreach ($quoteData['carrier_rows'] ?? [] as $row) {
            $typeStr = strtolower($row['type'] ?? '');
            $isSurcharge = ($typeStr === 'fuel (surcharge)');
            $qty = (float) ($row['qty'] ?? 0);
            $rate = (float) ($row['rate'] ?? 0);

            $cost = $isSurcharge
                ? round($carrierFreightBase * ($qty / 100), 2)
                : round($qty * $rate, 2);

            $quote->costs()->create([
                'category' => 'carrier',
                'type' => $row['type'] ?? 'Freight',
                'description' => $row['description'] ?? '',
                'qty' => $qty,
                'rate' => $isSurcharge ? $carrierFreightBase : $rate,
                'cost' => $cost,
                'percentage' => $isSurcharge ? $qty : null,
            ]);
        }

        $log->info('Processed quote', [
            'quote_id' => $quote->id,
            'service_id' => $quote->service_id,
            'customer_rows' => count($quoteData['customer_rows'] ?? []),
            'carrier_rows' => count($quoteData['carrier_rows'] ?? []),
        ]);
    }

    protected function sanitizeCoordinate(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return null;
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

    protected function parseOrderDateTime($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $formats = [
            'm/d/y H:i',
            'm/d/Y H:i',
            'Y-m-d H:i',
            'Y-m-d\TH:i',
            'Y-m-d H:i:s',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d H:i:s');
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function parseQuoteDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        $formats = [
            'Y-m-d',
            'Y-m-d\\TH:i',
            'Y-m-d H:i',
            'm/d/y H:i',
            'm/d/Y H:i',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->format('Y-m-d');
            } catch (\Throwable $e) {
                // Try next format.
            }
        }

        try {
            return Carbon::parse($value)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function quoteHasContent(array $quoteData): bool
    {
        foreach (['customer_rows', 'carrier_rows'] as $key) {
            foreach (($quoteData[$key] ?? []) as $row) {
                if (!empty($row['description']) || (!empty($row['cost']) && (float) $row['cost'] != 0.0)) {
                    return true;
                }
            }
        }

        return !empty($quoteData['service_id']) || !empty($quoteData['delivery_start']) || !empty($quoteData['delivery_end']);
    }
}
