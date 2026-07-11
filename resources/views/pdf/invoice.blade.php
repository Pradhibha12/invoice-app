<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #374151; line-height: 1.5; }
        .header { margin-bottom: 30px; border-bottom: 2px solid #e5e7eb; padding-bottom: 20px; }
        .title { font-size: 24px; font-weight: bold; color: #0f766e; }
        .details-table { width: 100%; margin-bottom: 25px; }
        .details-table td { vertical-align: top; }
        .items-table { width: 100%; border-collapse: collapse; margin-top: 25px; margin-bottom: 25px; }
        .items-table th, .items-table td { border: 1px solid #e5e7eb; padding: 10px; text-align: left; }
        .items-table th { background-color: #f9fafb; font-weight: bold; color: #4b5563; }
        .text-right { text-align: right; }
        .badge { display: inline-block; padding: 3px 8px; border-radius: 9999px; font-size: 10px; font-weight: bold; text-transform: uppercase; color: #ffffff; }
        .badge-draft { background-color: #6b7280; }
        .badge-sent { background-color: #0284c7; }
        .badge-partially_paid { background-color: #f97316; }
        .badge-paid { background-color: #059669; }
        .badge-overdue { background-color: #e11d48; }
        
        .summary-box { float: right; width: 280px; margin-top: 15px; }
        .summary-table { width: 100%; border-collapse: collapse; }
        .summary-table td { padding: 4px 8px; }
        
        .logo-container { width: 60px; height: 60px; overflow: hidden; margin-right: 15px; }
    </style>
</head>
<body>
    @php
        $f = function($amt) {
            return str_replace('₹', '&#8377;', \App\Models\Invoice::formatINR($amt));
        };
    @endphp

    <!-- Header -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: middle;">
                    <table style="border: none;">
                        <tr>
                            @if ($company->logo_path && file_exists(storage_path('app/public/' . $company->logo_path)))
                                <td style="padding-right: 15px;">
                                    <img src="{{ storage_path('app/public/' . $company->logo_path) }}" style="max-height: 60px; max-width: 120px; object-fit: contain;">
                                </td>
                            @endif
                            <td>
                                <div class="title">{{ $company->company_name }}</div>
                                @if($company->tax_id)
                                    <div style="font-size: 11px; color: #6b7280;">Tax ID: {{ $company->tax_id }}</div>
                                @endif
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="text-right" style="vertical-align: middle;">
                    <strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
                    <strong>Status:</strong> <span class="badge badge-{{ $invoice->status }}">{{ str_replace('_', ' ', $invoice->status) }}</span><br>
                    <strong>Issue Date:</strong> {{ $invoice->issue_date }}<br>
                    <strong>Due Date:</strong> {{ $invoice->due_date }}
                </td>
            </tr>
        </table>
    </div>

    <!-- Contact details -->
    <table class="details-table">
        <tr>
            <td style="width: 50%;">
                <h3 style="margin-top: 0; color: #0f766e; font-size: 14px;">Billed To:</h3>
                <strong>{{ $invoice->client->name }}</strong><br>
                {{ $invoice->client->email }}<br>
                @if($invoice->client->phone)
                    Phone: {{ $invoice->client->phone }}<br>
                @endif
                @if($invoice->client->address)
                    {!! nl2br(e($invoice->client->address)) !!}
                @endif
            </td>
            <td class="text-right" style="width: 50%;">
                <h3 style="margin-top: 0; color: #0f766e; font-size: 14px;">From:</h3>
                @if($company->address)
                    {!! nl2br(e($company->address)) !!}<br>
                @endif
                @if($company->email)
                    Email: {{ $company->email }}<br>
                @endif
                @if($company->phone)
                    Phone: {{ $company->phone }}
                @endif
            </td>
        </tr>
    </table>

    <!-- Line items -->
    <table class="items-table">
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right" style="width: 80px;">Quantity</th>
                <th class="text-right" style="width: 100px;">Unit Price</th>
                <th class="text-right" style="width: 120px;">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @php
                $subtotal = 0;
            @endphp
            @foreach($invoice->items as $item)
                @php
                    $subtotal += $item->line_total;
                @endphp
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">{!! $f($item->unit_price) !!}</td>
                    <td class="text-right">{!! $f($item->line_total) !!}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Summary, History & Notes -->
    <div style="width: 100%;">
        <div style="float: left; width: 55%;">
            @if($invoice->payments->count() > 0)
                <div style="margin-bottom: 20px; padding-right: 20px;">
                    <strong style="color: #374151; font-size: 12px;">Payment History:</strong><br>
                    <table style="width: 100%; border-collapse: collapse; font-size: 11px; margin-top: 5px;">
                        <thead>
                            <tr style="background-color: #f9fafb; border-bottom: 1px solid #e5e7eb;">
                                <th style="padding: 4px; text-align: left;">Paid On</th>
                                <th style="padding: 4px; text-align: left;">Mode</th>
                                <th style="padding: 4px; text-align: right;">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($invoice->payments as $payment)
                                <tr style="border-bottom: 1px solid #e5e7eb;">
                                    <td style="padding: 4px; color: #4b5563;">{{ $payment->paid_on }}</td>
                                    <td style="padding: 4px; color: #4b5563;">{{ $payment->payment_mode }}</td>
                                    <td style="padding: 4px; color: #111827; text-align: right; font-weight: bold;">{!! $f($payment->amount) !!}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            @if($invoice->notes)
                <div style="font-size: 11px; color: #6b7280; padding-right: 20px; margin-top: 10px;">
                    <strong style="color: #374151; font-size: 12px;">Notes & Terms:</strong><br>
                    {!! nl2br(e($invoice->notes)) !!}
                </div>
            @endif
        </div>

        <div class="summary-box">
            <table class="summary-table">
                <tr>
                    <td style="color: #6b7280; font-size: 12px;">Subtotal:</td>
                    <td class="text-right" style="font-size: 12px;">{!! $f($subtotal) !!}</td>
                </tr>
                @if($invoice->discount > 0)
                    @php
                        $discountAmount = $subtotal * ($invoice->discount / 100);
                        $subtotal = $subtotal - $discountAmount;
                    @endphp
                    <tr>
                        <td style="color: #059669; font-size: 12px;">Discount ({{ $invoice->discount }}%):</td>
                        <td class="text-right" style="color: #059669; font-size: 12px;">-{!! $f($discountAmount) !!}</td>
                    </tr>
                @endif
                @if($invoice->tax_rate > 0)
                    @php
                        $taxAmount = $subtotal * ($invoice->tax_rate / 100);
                    @endphp
                    <tr>
                        <td style="color: #6b7280; font-size: 12px;">Tax ({{ $invoice->tax_rate }}%):</td>
                        <td class="text-right" style="font-size: 12px;">+{!! $f($taxAmount) !!}</td>
                    </tr>
                @endif
                <tr style="border-top: 1px solid #e5e7eb;">
                    <td style="font-weight: bold; padding-top: 8px; color: #111827; font-size: 12px;">Grand Total:</td>
                    <td class="text-right" style="font-weight: bold; font-size: 14px; color: #0f766e; padding-top: 8px;">{!! $f($invoice->total) !!}</td>
                </tr>
                <tr>
                    <td style="color: #059669; font-weight: bold; padding-top: 4px; font-size: 12px;">Amount Paid:</td>
                    <td class="text-right" style="color: #059669; font-weight: bold; padding-top: 4px; font-size: 12px;">-{!! $f($invoice->amount_paid) !!}</td>
                </tr>
                <tr style="border-top: 1px double #e5e7eb;">
                    <td style="font-weight: bold; padding-top: 6px; color: #111827; font-size: 12px;">Balance Due:</td>
                    <td class="text-right" style="font-weight: bold; font-size: 14px; color: #0f766e; padding-top: 6px;">{!! $f($invoice->total - $invoice->amount_paid) !!}</td>
                </tr>
            </table>

            @if ($invoice->payment_mode && $invoice->amount_paid > 0)
                <div style="font-size: 10px; color: #6b7280; text-align: right; margin-top: 10px; font-weight: bold;">
                    Payment Mode: {{ $invoice->payment_mode }}
                </div>
            @endif
        </div>
        <div style="clear: both;"></div>
    </div>
</body>
</html>
