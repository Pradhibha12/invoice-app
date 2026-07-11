<?php

use Livewire\Component;
use App\Models\Invoice;
use App\Models\Client;
use Illuminate\Support\Facades\DB;

new class extends Component
{
    public $invoice = null;
    public $clients = [];

    public $client_id = null;
    public $invoice_number = '';
    public $issue_date = '';
    public $due_date = '';
    public $status = 'draft';
    public $notes = '';
    public $total = 0.00;
    
    // Calculated live properties
    public $subtotal = 0.00;
    public $discount_amount = 0.00;
    public $tax_amount = 0.00;

    // Extended columns
    public $tax_rate = 0.00;
    public $discount = 0.00;
    public $payment_mode = '';

    public $items = [];

    public function mount(?Invoice $invoice = null)
    {
        $this->clients = Client::all();

        if ($invoice && $invoice->exists) {
            $this->invoice = $invoice;
            $this->client_id = $invoice->client_id;
            $this->invoice_number = $invoice->invoice_number;
            $this->issue_date = $invoice->issue_date;
            $this->due_date = $invoice->due_date;
            $this->status = $invoice->status;
            $this->notes = $invoice->notes ?? '';
            $this->tax_rate = floatval($invoice->tax_rate);
            $this->discount = floatval($invoice->discount);
            $this->payment_mode = $invoice->payment_mode ?? '';
            
            $this->items = $invoice->items()->get()->map(function ($item) {
                return [
                    'description' => $item->description,
                    'quantity' => intval($item->quantity),
                    'unit_price' => floatval($item->unit_price),
                    'line_total' => floatval($item->line_total),
                ];
            })->toArray();

            $this->calculateTotals();
        } else {
            $this->issue_date = now()->format('Y-m-d');
            $this->due_date = now()->addDays(30)->format('Y-m-d');
            $this->invoice_number = 'INV-' . date('Y') . '-' . rand(1000, 9999);
            $this->addItem();
        }
    }

    public function addItem()
    {
        $this->items[] = [
            'description' => '',
            'quantity' => 1,
            'unit_price' => 0.00,
            'line_total' => 0.00,
        ];
        $this->calculateTotals();
    }

    public function removeItem(int $index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
        $this->calculateTotals();
    }

    public function updated($name, $value)
    {
        if (str_starts_with($name, 'items.') || $name === 'tax_rate' || $name === 'discount') {
            $this->calculateTotals();
        }
    }

    public function calculateTotals()
    {
        $this->subtotal = 0;
        foreach ($this->items as $index => $item) {
            $qty = floatval($item['quantity'] ?? 0);
            $price = floatval($item['unit_price'] ?? 0);
            $lineTotal = $qty * $price;
            $this->items[$index]['line_total'] = $lineTotal;
            $this->subtotal += $lineTotal;
        }
        
        $this->discount_amount = $this->subtotal * (floatval($this->discount) / 100);
        $taxableAmount = $this->subtotal - $this->discount_amount;
        $this->tax_amount = $taxableAmount * (floatval($this->tax_rate) / 100);
        
        $this->total = round($taxableAmount + $this->tax_amount, 2);
    }

    protected function rules()
    {
        return [
            'client_id' => 'required|exists:clients,id',
            'invoice_number' => 'required|string|unique:invoices,invoice_number,' . ($this->invoice->id ?? 'NULL'),
            'issue_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:issue_date',
            'status' => 'required|in:draft,sent,paid,overdue,partially_paid',
            'notes' => 'nullable|string',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'discount' => 'required|numeric|min:0|max:100',
            'payment_mode' => 'nullable|string|in:Cash,Bank Transfer,UPI,Cheque,Card,Other',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ];
    }

    public function save()
    {
        $this->calculateTotals();
        $this->validate();

        DB::transaction(function () {
            $invoiceData = [
                'client_id' => $this->client_id,
                'invoice_number' => $this->invoice_number,
                'issue_date' => $this->issue_date,
                'due_date' => $this->due_date,
                'status' => $this->status,
                'notes' => $this->notes,
                'tax_rate' => $this->tax_rate,
                'discount' => $this->discount,
                'total' => $this->total,
                'payment_mode' => $this->payment_mode ?: null,
            ];

            if ($this->invoice && $this->invoice->exists) {
                $this->invoice->update($invoiceData);
                $invoice = $this->invoice;
            } else {
                $invoice = Invoice::create($invoiceData);
            }

            // Sync items
            $invoice->items()->delete();
            foreach ($this->items as $item) {
                $invoice->items()->create([
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                ]);
            }

            $invoice->recalculateTotal();
        });

        session()->flash('message', $this->invoice ? 'Invoice updated successfully.' : 'Invoice created successfully.');

        return redirect()->route('invoices.index');
    }
};
?>

<div class="max-w-4xl mx-auto bg-white p-6 rounded-xl border border-stone-200 shadow-sm">
    <div class="mb-6 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-stone-900 font-display">{{ $invoice ? 'Edit Invoice' : 'Create Invoice' }}</h1>
        <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900">&larr; Back to List</a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label for="client_id" class="block text-sm font-semibold text-stone-700">Client</label>
                <select wire:model.live="client_id" id="client_id" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                    <option value="">Select a Client</option>
                    @foreach($clients as $c)
                        <option value="{{ $c->id }}">{{ $c->name }} ({{ $c->email }})</option>
                    @endforeach
                </select>
                @error('client_id') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="invoice_number" class="block text-sm font-semibold text-stone-700">Invoice Number</label>
                <input wire:model="invoice_number" type="text" id="invoice_number" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                @error('invoice_number') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <label for="issue_date" class="block text-sm font-semibold text-stone-700">Issue Date</label>
                <input wire:model="issue_date" type="date" id="issue_date" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                @error('issue_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="due_date" class="block text-sm font-semibold text-stone-700">Due Date</label>
                <input wire:model="due_date" type="date" id="due_date" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                @error('due_date') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            <div>
                <label for="status" class="block text-sm font-semibold text-stone-700">Status</label>
                <select wire:model="status" id="status" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                    <option value="draft">Draft</option>
                    <option value="sent">Sent</option>
                    <option value="partially_paid">Partially Paid</option>
                    <option value="paid">Paid</option>
                    <option value="overdue">Overdue</option>
                </select>
                @error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Line Items -->
        <div class="space-y-4">
            <div class="flex justify-between items-center border-b border-stone-200 pb-2">
                <h2 class="text-lg font-bold text-stone-900 font-display">Line Items</h2>
                <button type="button" wire:click="addItem" class="px-3.5 py-1.5 bg-teal-50 text-teal-700 hover:bg-teal-100 rounded-lg text-sm font-bold transition">
                    + Add Item
                </button>
            </div>

            @error('items') <span class="text-red-500 text-xs block">{{ $message }}</span> @enderror

            <div class="space-y-3">
                @foreach($items as $index => $item)
                    <div class="flex flex-col md:flex-row md:items-end gap-4 p-4 bg-stone-50 rounded-lg border border-stone-200 relative group">
                        <div class="flex-grow">
                            <label class="block text-xs font-semibold text-stone-500">Description</label>
                            <input wire:model.live="items.{{ $index }}.description" type="text" placeholder="Item description" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                            @error("items.{$index}.description") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full md:w-24">
                            <label class="block text-xs font-semibold text-stone-500">Quantity</label>
                            <input wire:model.live="items.{{ $index }}.quantity" type="number" min="1" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                            @error("items.{$index}.quantity") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full md:w-32">
                            <label class="block text-xs font-semibold text-stone-500">Unit Price (₹)</label>
                            <input wire:model.live="items.{{ $index }}.unit_price" type="number" step="0.01" min="0" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                            @error("items.{$index}.unit_price") <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>

                        <div class="w-full md:w-32 flex flex-col justify-end">
                            <span class="text-xs font-semibold text-stone-500">Line Total</span>
                            <div class="mt-2 h-9 flex items-center text-sm font-bold text-stone-900">
                                {{ \App\Models\Invoice::formatINR($item['line_total'] ?? 0) }}
                            </div>
                        </div>

                        <div class="flex items-center justify-end md:pb-2">
                            <button type="button" wire:click="removeItem({{ $index }})" class="text-rose-600 hover:text-rose-800 text-sm font-bold">
                                Remove
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Extended Fields (Tax & Discount) & Calculation Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-6 border-t border-stone-200">
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="discount" class="block text-sm font-semibold text-stone-700">Discount (%)</label>
                        <input wire:model.live="discount" type="number" step="0.01" min="0" max="100" id="discount" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                        @error('discount') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label for="tax_rate" class="block text-sm font-semibold text-stone-700">Tax Rate (%)</label>
                        <input wire:model.live="tax_rate" type="number" step="0.01" min="0" max="100" id="tax_rate" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                        @error('tax_rate') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="payment_mode" class="block text-sm font-semibold text-stone-700">Default Payment Mode</label>
                        <select wire:model="payment_mode" id="payment_mode" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm">
                            <option value="">Select Payment Mode</option>
                            <option value="Cash">Cash</option>
                            <option value="Bank Transfer">Bank Transfer</option>
                            <option value="UPI">UPI</option>
                            <option value="Cheque">Cheque</option>
                            <option value="Card">Card</option>
                            <option value="Other">Other</option>
                        </select>
                        @error('payment_mode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div>
                    <label for="notes" class="block text-sm font-semibold text-stone-700">Notes (Optional)</label>
                    <textarea wire:model="notes" id="notes" rows="3" class="mt-1 block w-full rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 text-sm" placeholder="Terms, payment methods, bank info..."></textarea>
                    @error('notes') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Totals Live breakdown card -->
            <div class="bg-stone-50 p-6 rounded-xl border border-stone-200 space-y-3 flex flex-col justify-between">
                <div class="space-y-2">
                    <div class="flex justify-between text-sm text-stone-500 font-semibold">
                        <span>Subtotal:</span>
                        <span>{{ \App\Models\Invoice::formatINR($subtotal) }}</span>
                    </div>
                    @if($discount > 0)
                        <div class="flex justify-between text-sm text-emerald-700 font-semibold">
                            <span>Discount ({{ $discount }}%):</span>
                            <span>-{{ \App\Models\Invoice::formatINR($discount_amount) }}</span>
                        </div>
                    @endif
                    @if($tax_rate > 0)
                        <div class="flex justify-between text-sm text-stone-500 font-semibold">
                            <span>Tax ({{ $tax_rate }}%):</span>
                            <span>+{{ \App\Models\Invoice::formatINR($tax_amount) }}</span>
                        </div>
                    @endif
                </div>

                <div class="pt-4 border-t border-stone-200 flex justify-between items-baseline">
                    <span class="text-sm font-bold text-stone-700">Grand Total:</span>
                    <span class="text-3xl font-black text-stone-900 font-display">{{ \App\Models\Invoice::formatINR($total) }}</span>
                </div>
            </div>
        </div>

        <div class="flex justify-end pt-4 border-t border-stone-100 space-x-2">
            <a href="{{ route('invoices.index') }}" class="px-4 py-2 border border-stone-300 rounded-lg shadow-sm text-sm font-medium text-stone-700 bg-white hover:bg-stone-50 transition">
                Cancel
            </a>
            <button type="submit" class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 shadow-sm transition">
                Save Invoice
            </button>
        </div>
    </form>
</div>