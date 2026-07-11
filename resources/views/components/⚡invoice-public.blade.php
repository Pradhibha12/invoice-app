<?php

use Livewire\Component;
use App\Models\Invoice;
use App\Models\CompanySetting;

new class extends Component
{
    public Invoice $invoice;
    public CompanySetting $company;

    public function mount(string $token)
    {
        $this->invoice = Invoice::where('token', $token)->with(['client', 'items', 'payments'])->firstOrFail();
        $this->company = CompanySetting::getOrNew();
    }

    public function getSubtotal(): float
    {
        return floatval($this->invoice->items->sum('line_total'));
    }

    public function getDiscountAmount(): float
    {
        return $this->getSubtotal() * (floatval($this->invoice->discount) / 100);
    }

    public function getTaxAmount(): float
    {
        $taxable = $this->getSubtotal() - $this->getDiscountAmount();
        return $taxable * (floatval($this->invoice->tax_rate) / 100);
    }

    public function getOverdueDays(): int
    {
        if ($this->invoice->status === 'paid') return 0;
        $due = \Carbon\Carbon::parse($this->invoice->due_date);
        $today = now()->startOfDay();
        if ($today->greaterThan($due)) {
            return $due->diffInDays($today);
        }
        return 0;
    }
};
?>

<div class="min-h-screen bg-stone-50 py-12 px-4 sm:px-6 lg:px-8 w-full flex flex-col items-center">
    <div class="space-y-6 w-full max-w-4xl">
        <!-- Public top bar -->
        <div class="flex justify-between items-center bg-white p-4 rounded-xl border border-stone-200 shadow-sm no-print">
            <div class="flex items-center space-x-2">
                <span class="inline-block w-2.5 h-2.5 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-xs font-bold text-stone-500 uppercase tracking-wider">Client-Facing Link</span>
            </div>
            
            <button onclick="window.print()" class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-stone-950 rounded-lg text-sm font-bold shadow-sm transition cursor-pointer">
                Print Invoice
            </button>
        </div>

        <!-- Payment status banner -->
        <div class="overflow-hidden rounded-xl border shadow-sm">
            @if ($invoice->status === 'paid')
                <div class="bg-emerald-600 text-white px-6 py-4 flex justify-between items-center">
                    <span class="font-bold text-lg font-display">Paid Invoice</span>
                    <span class="text-sm font-semibold">Payment completed on {{ $invoice->paid_date }}</span>
                </div>
            @elseif ($invoice->status === 'partially_paid')
                <div class="bg-orange-500 text-white px-6 py-4 flex justify-between items-center">
                    <span class="font-bold text-lg font-display">Partially Paid</span>
                    <span class="text-sm font-semibold">Remaining Balance: {{ \App\Models\Invoice::formatINR($invoice->total - $invoice->amount_paid) }}</span>
                </div>
            @elseif ($this->getOverdueDays() > 0)
                <div class="bg-rose-600 text-white px-6 py-4 flex justify-between items-center">
                    <span class="font-bold text-lg font-display">Overdue Invoice</span>
                    <span class="text-sm font-semibold">Overdue by {{ $this->getOverdueDays() }} days</span>
                </div>
            @else
                <div class="bg-sky-600 text-white px-6 py-4 flex justify-between items-center">
                    <span class="font-bold text-lg font-display">Outstanding Invoice</span>
                    <span class="text-sm font-semibold">Payment due on {{ $invoice->due_date }}</span>
                </div>
            @endif
        </div>

        <!-- Invoice Sheet -->
        <div class="bg-white p-8 rounded-xl border border-stone-200 shadow-sm space-y-8">
            <!-- Brand / Logo & Company Info Header -->
            <div class="flex flex-col md:flex-row justify-between items-start border-b border-stone-200 pb-8 gap-6">
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

                <div class="text-left md:text-right text-sm text-stone-600 space-y-1">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
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

                <div class="md:text-right space-y-2">
                    <div>
                        <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block">Invoice Number</span>
                        <span class="text-lg font-extrabold text-stone-950 font-display">{{ $invoice->invoice_number }}</span>
                    </div>
                    <div class="grid grid-cols-2 gap-4 md:flex md:flex-col md:space-y-2">
                        <div>
                            <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block">Issue Date</span>
                            <span class="text-sm font-semibold text-stone-800">{{ $invoice->issue_date }}</span>
                        </div>
                        <div>
                            <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block">Due Date</span>
                            <span class="text-sm font-semibold text-stone-800">{{ $invoice->due_date }}</span>
                        </div>
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
                        @foreach($invoice->items as $item)
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

            <!-- Financial Summary breakdown, Payments, & Notes -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 pt-4">
                <div class="md:col-span-2 space-y-6">
                    <!-- Payment History -->
                    @if($invoice->payments->count() > 0)
                        <div>
                            <span class="text-xs font-bold text-stone-500 uppercase tracking-widest block mb-2 font-display">Payment History</span>
                            <div class="border border-stone-200 rounded-xl overflow-hidden shadow-sm">
                                <table class="min-w-full divide-y divide-stone-200 text-xs">
                                    <thead class="bg-stone-50">
                                        <tr>
                                            <th scope="col" class="px-4 py-2.5 text-left font-bold text-stone-500 uppercase">Paid On</th>
                                            <th scope="col" class="px-4 py-2.5 text-left font-bold text-stone-500 uppercase">Payment Mode</th>
                                            <th scope="col" class="px-4 py-2.5 text-right font-bold text-stone-500 uppercase">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-stone-200">
                                        @foreach($invoice->payments as $payment)
                                            <tr class="hover:bg-stone-50/50 transition">
                                                <td class="px-4 py-2 whitespace-nowrap text-stone-600 font-semibold">{{ $payment->paid_on }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-stone-600 font-semibold">{{ $payment->payment_mode }}</td>
                                                <td class="px-4 py-2 whitespace-nowrap text-stone-950 font-bold text-right">{{ \App\Models\Invoice::formatINR($payment->amount) }}</td>
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
                            <p class="text-sm text-stone-600 whitespace-pre-line bg-stone-50 p-4 rounded-xl border border-stone-200 leading-relaxed">{{ $invoice->notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- Pricing Math Card & Payment Summary Box -->
                <div class="space-y-4">
                    <div class="space-y-2 border-b border-stone-100 pb-4">
                        <div class="flex justify-between text-sm text-stone-500 font-semibold">
                            <span>Subtotal:</span>
                            <span>{{ \App\Models\Invoice::formatINR($this->getSubtotal()) }}</span>
                        </div>
                        
                        @if($invoice->discount > 0)
                            <div class="flex justify-between text-sm text-emerald-700 font-semibold">
                                <span>Discount ({{ $invoice->discount }}%):</span>
                                <span>-{{ \App\Models\Invoice::formatINR($this->getDiscountAmount()) }}</span>
                            </div>
                        @endif

                        @if($invoice->tax_rate > 0)
                            <div class="flex justify-between text-sm text-stone-500 font-semibold">
                                <span>Tax ({{ $invoice->tax_rate }}%):</span>
                                <span>+{{ \App\Models\Invoice::formatINR($this->getTaxAmount()) }}</span>
                            </div>
                        @endif
                    </div>

                    <!-- Payment Summary Box -->
                    <div class="bg-stone-50 p-5 rounded-xl border border-stone-200 space-y-3">
                        <div class="flex justify-between text-sm font-semibold text-stone-700">
                            <span>Total Amount:</span>
                            <span class="font-bold text-stone-900">{{ \App\Models\Invoice::formatINR($invoice->total) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-semibold text-emerald-800">
                            <span>Amount Paid:</span>
                            <span class="font-bold">{{ \App\Models\Invoice::formatINR($invoice->amount_paid) }}</span>
                        </div>
                        <div class="flex justify-between text-sm font-bold border-t border-stone-200 pt-3 text-stone-900">
                            <span>Pending Balance:</span>
                            <span class="text-xl font-black text-teal-700 font-display">{{ \App\Models\Invoice::formatINR($invoice->total - $invoice->amount_paid) }}</span>
                        </div>
                    </div>

                    @if ($invoice->payment_mode && $invoice->amount_paid > 0)
                        <div class="text-xs text-stone-500 font-semibold mt-1">
                            <span>Payment Mode:</span> <span class="text-stone-800">{{ $invoice->payment_mode }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        .no-print {
            display: none !important;
        }
        body {
            background-color: white !important;
            padding: 0 !important;
        }
        .min-h-screen {
            min-height: auto !important;
            padding: 0 !important;
        }
    }
</style>
