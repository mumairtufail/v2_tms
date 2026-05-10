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
                        @if($company->logo)
                            <img src="{{ public_path('storage/'.$company->logo) }}" alt="Logo">
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
            
            @php
                $allStops = collect();
                
                // Add Manual Stops
                foreach($manifest->stops as $stop) {
                    $allStops->push((object)[
                        'type' => 'Manual',
                        'order_val' => $stop->id, // Use ID as fallback order
                        'stop_type' => 'STOP',
                        'location_name' => $stop->location ?? $stop->company,
                        'address' => $stop->address1 . ($stop->address2 ? ' ' . $stop->address2 : ''),
                        'city' => $stop->city,
                        'state' => $stop->state,
                        'zip' => $stop->postal,
                        'scheduled_date' => null,
                        'scheduled_time' => null,
                        'instructions' => null,
                        'order_ref' => null,
                        'commodities' => collect(),
                        'container' => null,
                        'p_o_number' => null,
                        'ref_number' => null,
                    ]);
                }
                
                // Add Order Stops
                foreach($manifest->orderStops as $oStop) {
                    $order = $oStop->order;
                    $allStops->push((object)[
                        'type' => 'Order',
                        'order_val' => $oStop->stop_order ?? 0,
                        'stop_type' => $oStop->stop_type,
                        'location_name' => $oStop->location_name,
                        'address' => $oStop->address,
                        'city' => $oStop->city,
                        'state' => $oStop->state,
                        'zip' => $oStop->zip,
                        'scheduled_date' => $oStop->scheduled_date,
                        'scheduled_time' => $oStop->scheduled_time_range,
                        'instructions' => $oStop->instructions,
                        'order_ref' => $order ? $order->order_number : null,
                        'commodities' => $oStop->commodities,
                        'container' => $order ? $order->container_number : null,
                        'p_o_number' => $order ? $order->customer_po_number : null,
                        'ref_number' => $order ? $order->ref_number : null,
                    ]);
                }
                
                // Sort by order_val
                $sortedStops = $allStops->sortBy('order_val');
                $stopIndex = 1;
            @endphp

            @foreach($sortedStops as $stop)
                <div class="stop">
                    <div class="stop-header">
                        <span class="stop-type">{{ $stop->stop_type }}</span>
                        <span class="stop-number">STOP #{{ $stopIndex++ }}</span>
                    </div>
                    <div class="stop-body">
                        <table class="stop-table">
                            <tr>
                                <td class="stop-address-cell">
                                    <div class="stop-label">Location</div>
                                    <div class="stop-value">{{ $stop->location_name }}</div>
                                    <div class="info-details">
                                        {{ $stop->address }}<br>
                                        {{ $stop->city }}, {{ $stop->state }} {{ $stop->zip }}
                                    </div>

                                    @if($stop->order_ref || $stop->container || $stop->p_o_number || $stop->ref_number)
                                        <div class="ref-tags">
                                            @if($stop->order_ref)<span class="ref-tag">Order: {{ $stop->order_ref }}</span>@endif
                                            @if($stop->container)<span class="ref-tag">Cont: {{ $stop->container }}</span>@endif
                                            @if($stop->p_o_number)<span class="ref-tag">PO: {{ $stop->p_o_number }}</span>@endif
                                            @if($stop->ref_number)<span class="ref-tag">REF: {{ $stop->ref_number }}</span>@endif
                                        </div>
                                    @endif
                                </td>
                                <td class="stop-details-cell">
                                    <div class="stop-label">Schedule</div>
                                    <div class="stop-value">
                                        {{ $stop->scheduled_date ? date('M d, Y', strtotime($stop->scheduled_date)) : 'N/A' }}
                                        @if($stop->scheduled_time)
                                            <br>{{ $stop->scheduled_time }}
                                        @endif
                                    </div>
                                    
                                    @if($stop->instructions)
                                        <div class="stop-label">Instructions</div>
                                        <div style="font-size: 10px;">{{ $stop->instructions }}</div>
                                    @endif
                                </td>
                            </tr>
                        </table>

                        @if($stop->commodities && $stop->commodities->count() > 0)
                            <table class="commodity-table">
                                <thead>
                                    <tr>
                                        <th>Commodity</th>
                                        <th>Qty</th>
                                        <th>Weight</th>
                                        <th>Dimensions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($stop->commodities as $commodity)
                                        <tr>
                                            <td>{{ $commodity->description }}</td>
                                            <td>{{ $commodity->quantity }} {{ $commodity->quantity_unit }}</td>
                                            <td>{{ $commodity->weight }} {{ $commodity->weight_unit }}</td>
                                            <td>{{ $commodity->length }}x{{ $commodity->width }}x{{ $commodity->height }} {{ $commodity->dimension_unit }}</td>
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
                        <th style="text-align: right">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @php $total = 0; @endphp
                    @if($manifest->costEstimates->count() > 0)
                        @foreach($manifest->costEstimates as $estimate)
                            <tr>
                                <td>{{ $estimate->description ?: 'Carrier Charge' }}</td>
                                <td style="text-align: right">${{ number_format($estimate->amount, 2) }}</td>
                            </tr>
                            @php $total += $estimate->amount; @endphp
                        @endforeach
                    @else
                        {{-- Fallback --}}
                        @foreach($manifest->carriers as $carrier)
                            <tr>
                                <td>Linehaul - {{ $carrier->carrier_name }}</td>
                                <td style="text-align: right">${{ number_format($carrier->linehaul_cost, 2) }}</td>
                            </tr>
                            @php $total += $carrier->linehaul_cost; @endphp
                        @endforeach
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