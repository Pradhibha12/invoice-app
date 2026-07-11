<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Invoice - {{ $invoice->invoice_number }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=Instrument+Sans:wght@400;600;700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background-color: #fff !important;
                color: #000 !important;
            }
            .print-container {
                border: none !important;
                box-shadow: none !important;
                padding: 0 !important;
                margin: 0 !important;
                max-width: 100% !important;
            }
        }
    </style>
</head>
<body class="bg-stone-100 text-stone-900 font-sans antialiased min-h-screen py-8">

    <!-- Top Action Bar (Hidden during print) -->
    <div class="max-w-4xl mx-auto mb-6 px-6 py-4 bg-white rounded-xl border border-stone-200 shadow-sm flex justify-between items-center no-print">
        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices.show', $invoice) }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to View</a>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('invoices.edit', $invoice) }}" class="px-4 py-2 border border-stone-300 hover:bg-stone-50 text-stone-700 rounded-lg text-sm font-semibold transition">
                Edit Invoice
            </a>
            <button onclick="window.print()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-stone-950 rounded-lg text-sm font-bold shadow-sm transition">
                Print Now
            </button>
        </div>
    </div>

    <!-- Printable Invoice Sheet -->
    <div class="max-w-4xl mx-auto bg-white p-12 rounded-xl border border-stone-200 shadow-sm space-y-8 print-container">
        <!-- Brand / Logo & Company Info Header -->
        <div class="flex justify-between items-start border-b border-stone-200 pb-8 gap-6">
            <div class="flex items-center space-x-4">
                @if ($company->logo_path)
                    <div class="w-16 h-16 rounded-xl overflow-hidden border border-stone-200 bg-stone-50 flex items-center justify-center shadow-inner">
                        <img src="{{ asset('storage/' . $company->logo_path) }}" class="object-contain w-full h-full">
                    </div>
                @else
                    <div class="w-12 h-12 rounded-lg bg-teal-700 flex items-center justify-center font-black text-white text-lg font-display">
                        I
                    </div>
                @endif
                <div>
                    <h2 class="text-2xl font-black text-stone-900 font-display leading-tight">{{ $company->company_name }}</h2>
                    @if($company->tax_id)
                        <p class="text-xs font-semibold text-stone-500 mt-0.5">Tax ID: {{ $company->tax_id }}</p>
                    @endif
                </div>
            </div>

            <div class="text-right text-sm text-stone-600 space-y-1">
                @if($company->address)
                    <p class="whitespace-pre-line leading-relaxed">{{ $company->address }}</p>
                @endif
                @if($company->email)
                    <p>Email: {{ $company->email }}</p>
                @endif
                @if($company->phone)
                    <p>Phone: {{ $company->phone }}</p>
                @endif
            </div>
        </div>

        <!-- Billed to / Meta Grid -->
        <div class="grid grid-cols-2 gap-8">
            <div>
                <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block mb-2">Billed To</span>
                <p class="font-extrabold text-stone-950 text-lg leading-snug">{{ $invoice->client->name }}</p>
                <p class="text-sm text-stone-600 mt-1">{{ $invoice->client->email }}</p>
                @if($invoice->client->phone)
                    <p class="text-sm text-stone-600">{{ $invoice->client->phone }}</p>
                @endif
                @if($invoice->client->address)
                    <p class="text-sm text-stone-600 mt-2 whitespace-pre-line leading-relaxed">{{ $invoice->client->address }}</p>
                @endif
            </div>

            <div class="text-right space-y-2">
                <div>
                    <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block">Invoice Number</span>
                    <span class="text-lg font-extrabold text-stone-950 font-display">{{ $invoice->invoice_number }}</span>
                </div>
                <div>
                    <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block">Status</span>
                    <span class="text-sm font-bold uppercase tracking-wider text-stone-800">{{ $invoice->status }}</span>
                </div>
                <div class="space-y-1 pt-2">
                    <p class="text-sm"><span class="font-semibold text-stone-500">Issue Date:</span> <span class="text-stone-800">{{ $invoice->issue_date }}</span></p>
                    <p class="text-sm"><span class="font-semibold text-stone-500">Due Date:</span> <span class="text-stone-800">{{ $invoice->due_date }}</span></p>
                </div>
            </div>
        </div>

        <!-- Line items table -->
        <div class="border border-stone-200 rounded-xl overflow-hidden shadow-sm">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-bold text-stone-500 uppercase tracking-wider">Description</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-wider">Quantity</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-wider">Unit Price</th>
                        <th scope="col" class="px-6 py-4 text-right text-xs font-bold text-stone-500 uppercase tracking-wider">Line Total</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-200">
                    @php
                        $subtotal = 0;
                    @endphp
                    @foreach($invoice->items as $item)
                        @php
                            $subtotal += $item->line_total;
                        @endphp
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-900 font-semibold">{{ $item->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 text-right">{{ $item->quantity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 text-right">{{ \App\Models\Invoice::formatINR($item->unit_price) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-950 font-bold text-right">{{ \App\Models\Invoice::formatINR($item->line_total) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Financial Summary, Payments, History & Notes -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
            <div class="md:col-span-2 space-y-6">
                <!-- Payment History -->
                @if($invoice->payments->count() > 0)
                    <div>
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block mb-2">Payment History</span>
                        <div class="border border-stone-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-stone-200 text-xs">
                                <thead class="bg-stone-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left font-bold text-stone-500 uppercase">Date</th>
                                        <th scope="col" class="px-4 py-2 text-left font-bold text-stone-500 uppercase">Mode</th>
                                        <th scope="col" class="px-4 py-2 text-right font-bold text-stone-500 uppercase">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-stone-200">
                                    @foreach($invoice->payments as $payment)
                                        <tr>
                                            <td class="px-4 py-2 whitespace-nowrap text-stone-600 font-medium">{{ $payment->paid_on }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-stone-600 font-medium">{{ $payment->payment_mode }}</td>
                                            <td class="px-4 py-2 whitespace-nowrap text-stone-900 font-bold text-right">{{ \App\Models\Invoice::formatINR($payment->amount) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif

                @if($invoice->notes)
                    <div>
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block mb-2">Notes & Terms</span>
                        <p class="text-xs text-stone-600 whitespace-pre-line bg-stone-50 p-4 rounded-xl border border-stone-200 leading-relaxed">{{ $invoice->notes }}</p>
                    </div>
                @endif
            </div>

            <!-- Totals & Payment Summary Box -->
            <div class="space-y-4">
                <!-- Pricing calculation breakdown -->
                <div class="space-y-2 border-b border-stone-100 pb-4 text-sm">
                    <div class="flex justify-between text-stone-500 font-semibold">
                        <span>Subtotal:</span>
                        <span>{{ \App\Models\Invoice::formatINR($subtotal) }}</span>
                    </div>
                    @if($invoice->discount > 0)
                        @php
                            $discountAmount = $subtotal * ($invoice->discount / 100);
                            $subtotal = $subtotal - $discountAmount;
                        @endphp
                        <div class="flex justify-between text-emerald-700 font-semibold">
                            <span>Discount ({{ $invoice->discount }}%):</span>
                            <span>-{{ \App\Models\Invoice::formatINR($discountAmount) }}</span>
                        </div>
                    @endif
                    @if($invoice->tax_rate > 0)
                        @php
                            $taxAmount = $subtotal * ($invoice->tax_rate / 100);
                        @endphp
                        <div class="flex justify-between text-stone-500 font-semibold">
                            <span>Tax ({{ $invoice->tax_rate }}%):</span>
                            <span>+{{ \App\Models\Invoice::formatINR($taxAmount) }}</span>
                        </div>
                    @endif
                </div>

                <!-- Payment Summary Box -->
                <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 space-y-2.5">
                    <div class="flex justify-between text-sm font-semibold text-stone-700">
                        <span>Total Amount:</span>
                        <span class="font-bold text-stone-900">{{ \App\Models\Invoice::formatINR($invoice->total) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-semibold text-emerald-800">
                        <span>Amount Paid:</span>
                        <span class="font-bold">{{ \App\Models\Invoice::formatINR($invoice->amount_paid) }}</span>
                    </div>
                    <div class="flex justify-between text-sm font-bold border-t border-stone-200 pt-2.5 text-stone-900">
                        <span>Balance Due:</span>
                        <span class="text-lg font-black text-teal-700 font-display">{{ \App\Models\Invoice::formatINR($invoice->total - $invoice->amount_paid) }}</span>
                    </div>
                </div>

                @if($invoice->amount_paid > 0 && $invoice->payment_mode)
                    <div class="text-xs text-stone-500 font-semibold">
                        <span>Default Mode:</span> <span class="text-stone-800">{{ $invoice->payment_mode }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

</body>
</html>
