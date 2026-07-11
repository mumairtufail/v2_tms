<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Rate Confirmation #{{ $manifest->code }}</title>
    <style>
        @page {
            margin: 20px;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .container {
            padding: 10px;
        }
        .header {
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #111;
        }
        .subtitle {
            font-size: 12px;
            color: #2563eb;
            font-weight: bold;
            margin-top: 2px;
        }
        .logo {
            text-align: right;
        }
        .logo img {
            max-height: 50px;
            width: auto;
        }
        .info-grid {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .info-column {
            width: 50%;
            vertical-align: top;
            padding: 5px;
        }
        .section-title {
            font-size: 9px;
            font-weight: bold;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid #eee;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }
        .info-name {
            font-size: 14px;
            font-weight: bold;
            color: #111;
        }
        .info-details {
            font-size: 11px;
            color: #444;
            margin-top: 3px;
        }
        .meta-grid {
            margin-top: 10px;
            font-size: 10px;
        }
        .meta-item {
            margin-bottom: 2px;
        }
        .meta-label {
            color: #777;
            font-weight: bold;
            text-transform: uppercase;
            width: 100px;
            display: inline-block;
        }
        .meta-value {
            color: #111;
            font-weight: bold;
        }
        .stops-section {
            margin-bottom: 20px;
        }
        .stops-header-title {
            font-size: 11px;
            font-weight: bold;
            color: #fff;
            background: #111;
            padding: 5px 10px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .stop {
            margin-bottom: 15px;
            border: 1px solid #e5e7eb;
            page-break-inside: avoid;
        }
        .stop-header {
            background-color: #f3f4f6;
            padding: 5px 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .stop-type {
            font-weight: bold;
            color: #2563eb;
            font-size: 11px;
            text-transform: uppercase;
        }
        .stop-number {
            float: right;
            color: #666;
            font-weight: bold;
        }
        .stop-body {
            padding: 10px;
        }
        .stop-table {
            width: 100%;
            border-collapse: collapse;
        }
        .stop-address-cell {
            width: 50%;
            vertical-align: top;
        }
        .stop-details-cell {
            width: 50%;
            vertical-align: top;
            padding-left: 20px;
        }
        .stop-label {
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 5px;
        }
        .stop-value {
            font-weight: bold;
            color: #111;
        }
        .commodity-table {
            width: 100%;
            margin-top: 10px;
            border-top: 1px dashed #ccc;
            padding-top: 5px;
        }
        .commodity-table th {
            text-align: left;
            font-size: 9px;
            color: #888;
            text-transform: uppercase;
        }
        .commodity-table td {
            font-size: 10px;
        }
        .financial-container {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .financial-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .financial-table th {
            text-align: left;
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            padding: 8px;
            border-bottom: 1px solid #111;
        }
        .financial-table td {
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        .financial-total {
            background-color: #111;
            color: #fff;
            padding: 12px;
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 30px;
            font-size: 9px;
            color: #999;
            text-align: center;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
        .ref-tags {
            margin-top: 5px;
        }
        .ref-tag {
            background: #f0fdf4;
            color: #166534;
            padding: 2px 5px;
            border-radius: 3px;
            font-size: 9px;
            margin-right: 5px;
            display: inline-block;
            border: 1px solid #bbf7d0;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <table>
                <tr>
                    <td>
                        <div class="title">RATE CONFIRMATION</div>
                        <div class="subtitle">Confirmation # {{ $manifest->code }}</div>
                    </td>
                    <td class="logo">
                        @php
                            $logoPath = \App\Support\BrandingHelper::getLocalPath($company->logo_light);
                        @endphp
                        @if($logoPath)
                            <img src="{{ $logoPath }}" alt="Logo">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        <!-- Info Grid -->
        <table class="info-grid">
            <tr>
                <td class="info-column">
                    <div class="section-title">Carrier Information</div>
                    @if($manifest->carriers->count() > 0)
                        @foreach($manifest->carriers as $carrier)
                            <div class="info-name">{{ $carrier->carrier_name }}</div>
                            <div class="info-details">
                                {{ $carrier->address }}<br>
                                {{ $carrier->city }}, {{ $carrier->state }} {{ $carrier->zip }}<br>
                                @if($carrier->phone) Phone: {{ $carrier->phone }} @endif
                            </div>
                        @endforeach
                    @else
                        <div style="color: #ef4444; font-style: italic;">No Carrier Assigned</div>
                    @endif

                    <div class="meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">Date:</span>
                            <span class="meta-value">{{ date('M d, Y') }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Prepared By:</span>
                            <span class="meta-value">{{ $user->name }}</span>
                        </div>
                    </div>
                </td>
                <td class="info-column">
                    <div class="section-title">Issued By</div>
                    <div class="info-name">{{ $company->name }}</div>
                    <div class="info-details">
                        {{ $company->address }}<br>
                        {{ $company->city }}, {{ $company->state }} {{ $company->zip }}
                    </div>
                    
                    <div class="meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">Manifest ID:</span>
                            <span class="meta-value">{{ $manifest->code }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Status:</span>
                            <span class="meta-value">{{ strtoupper($manifest->status) }}</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Stops Section -->
        <div class="stops-section">
            <div class="stops-header-title">Routing & Stop Details</div>
            
            @php $stopIndex = 1; @endphp

            {{-- Manual Stops --}}
            @foreach($manifest->stops as $stop)
                <div class="stop">
                    <div class="stop-header">
                        <span class="stop-type">Manual Stop</span>
                        <span class="stop-number">STOP #{{ $stopIndex++ }}</span>
                    </div>
                    <div class="stop-body">
                        <div class="stop-label">Location</div>
                        <div class="stop-value">{{ $stop->location }}</div>
                        <div class="info-details">
                            {{ $stop->address1 }}{{ $stop->address2 ? ' ' . $stop->address2 : '' }}<br>
                            {{ $stop->city }}, {{ $stop->state }} {{ $stop->postal }}
                        </div>
                    </div>
                </div>
            @endforeach

            {{-- Order Stops --}}
            @foreach($manifest->orderStops as $oStop)
                @php
                    $consignee = $oStop->consignee_data ?? [];
                    $billing = $oStop->billing_data ?? [];

                    $readyStart = $consignee['shipper_ready_start_at'] ?? ($oStop->start_time ? $oStop->start_time->format('m/d/y H:i') : null);
                    $readyEnd = $consignee['shipper_ready_end_at'] ?? null;
                    $reqStart = $consignee['requested_start_at'] ?? ($oStop->end_time ? $oStop->end_time->format('m/d/y H:i') : null);
                    $reqEnd = $consignee['requested_end_at'] ?? null;
                @endphp
                <div class="stop">
                    <div class="stop-header">
                        <span class="stop-type">{{ $oStop->stop_type ?? 'Stop' }}</span>
                        <span class="stop-number">STOP #{{ $stopIndex++ }}</span>
                    </div>
                    <div class="stop-body">
                        <table class="stop-table">
                            <tr>
                                <td class="stop-address-cell">
                                    <div class="stop-label">Pickup (Shipper)</div>
                                    <div class="stop-value">{{ $oStop->company_name }}</div>
                                    <div class="info-details">
                                        {{ $oStop->address_1 }}{{ $oStop->address_2 ? ' ' . $oStop->address_2 : '' }}<br>
                                        {{ $oStop->city }}, {{ $oStop->state }} {{ $oStop->postal_code }}
                                        @if($oStop->contact_name || $oStop->contact_phone)
                                            <br>{{ trim(($oStop->contact_name ?? '') . ($oStop->contact_phone ? ' | ' . $oStop->contact_phone : '')) }}
                                        @endif
                                    </div>
                                </td>
                                <td class="stop-details-cell">
                                    @if(!empty(array_filter($consignee ?: [])))
                                        <div class="stop-label">Delivery (Consignee)</div>
                                        <div class="stop-value">{{ $consignee['company_name'] ?? 'N/A' }}</div>
                                        <div class="info-details">
                                            {{ $consignee['address_1'] ?? '' }}<br>
                                            {{ $consignee['city'] ?? '' }}{{ !empty($consignee['state']) ? ', ' . $consignee['state'] : '' }} {{ $consignee['zip'] ?? '' }}
                                            @if(!empty($consignee['contact_name']) || !empty($consignee['phone']))
                                                <br>{{ trim(($consignee['contact_name'] ?? '') . (!empty($consignee['phone']) ? ' | ' . $consignee['phone'] : '')) }}
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="stop-address-cell">
                                    @if($readyStart || $readyEnd)
                                        <div class="stop-label">Ready Window</div>
                                        <div class="stop-value">{{ $readyStart }}{{ $readyEnd ? ' - ' . $readyEnd : '' }}</div>
                                    @endif
                                </td>
                                <td class="stop-details-cell">
                                    @if($reqStart || $reqEnd)
                                        <div class="stop-label">Requested Window</div>
                                        <div class="stop-value">{{ $reqStart }}{{ $reqEnd ? ' - ' . $reqEnd : '' }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        @php
                            $orderRef = $oStop->order->order_number ?? null;
                            $container = $billing['container_number'] ?? null;
                            $poNumber = $billing['customer_po_number'] ?? null;
                            $refNumber = $billing['ref_number'] ?? null;
                        @endphp
                        @if($orderRef || $container || $poNumber || $refNumber)
                            <div class="ref-tags">
                                @if($orderRef)<span class="ref-tag">Order: {{ $orderRef }}</span>@endif
                                @if($container)<span class="ref-tag">Cont: {{ $container }}</span>@endif
                                @if($poNumber)<span class="ref-tag">PO: {{ $poNumber }}</span>@endif
                                @if($refNumber)<span class="ref-tag">REF: {{ $refNumber }}</span>@endif
                            </div>
                        @endif

                        @if($oStop->commodities->count() > 0)
                            <table class="commodity-table">
                                <thead>
                                    <tr>
                                        <th>Commodity</th>
                                        <th>Qty</th>
                                        <th>Type</th>
                                        <th>Weight</th>
                                        <th>Dimensions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($oStop->commodities as $commodity)
                                        <tr>
                                            <td>{{ $commodity->description }}</td>
                                            <td>{{ $commodity->quantity }}</td>
                                            <td>{{ $commodity->type }}</td>
                                            <td>{{ $commodity->weight }}</td>
                                            <td>{{ $commodity->length && $commodity->width && $commodity->height ? $commodity->length . 'x' . $commodity->width . 'x' . $commodity->height : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Financial Section -->
        <div class="financial-container">
            <div class="stops-header-title">Payment Terms</div>
            <table class="financial-table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right">Qty</th>
                        <th style="text-align: right">Rate</th>
                        <th style="text-align: right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @if($manifest->costEstimates->count() > 0)
                        @foreach($manifest->costEstimates as $estimate)
                            <tr>
                                <td>{{ $estimate->description ?: ucfirst($estimate->type ?? 'Carrier Charge') }}</td>
                                <td style="text-align: right">{{ $estimate->qty }}</td>
                                <td style="text-align: right">${{ number_format((float) $estimate->rate, 2) }}</td>
                                <td style="text-align: right">${{ number_format((float) $estimate->est_cost, 2) }}</td>
                            </tr>
                            @php $total += (float) $estimate->est_cost; @endphp
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" style="color: #999; font-style: italic; text-align: center;">No charges recorded</td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <div class="financial-total">
                <span style="font-size: 10px; text-transform: uppercase; margin-right: 20px;">Total Carrier Pay</span>
                ${{ number_format($total, 2) }}
            </div>
        </div>

        <div class="footer">
            By accepting this rate confirmation, the carrier agrees to all terms and conditions of the Broker-Carrier Agreement.<br>
            Generated on {{ date('M d, Y H:i:s') }} | Rate Confirmation Document
        </div>
    </div>
</body>
</html>