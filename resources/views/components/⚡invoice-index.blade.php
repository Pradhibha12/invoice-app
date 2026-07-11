<?php

use Livewire\Component;
use App\Models\Invoice;

new class extends Component
{
    public $statusFilter = '';

    public function delete(int $id)
    {
        $invoice = Invoice::find($id);
        if ($invoice) {
            $num = $invoice->invoice_number;
            $invoice->delete();
            \App\Models\ActivityLog::log('deleted', null, "Deleted Invoice {$num}");
        }
        session()->flash('message', 'Invoice deleted successfully.');
    }

    public function invoices()
    {
        $user = auth()->user();
        $query = Invoice::with('client')->latest();

        if ($user->role === 'client') {
            $query->where('client_id', $user->client_id);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return $query->get();
    }

    public function exportCsv()
    {
        $invoices = $this->invoices();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="invoices_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($invoices) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");

            fputcsv($file, [
                'Invoice Number',
                'Client Name',
                'Client Email',
                'Issue Date',
                'Due Date',
                'Status',
                'Total (INR)',
                'Amount Paid (INR)',
                'Balance (INR)',
                'Payment Mode'
            ]);

            foreach ($invoices as $invoice) {
                fputcsv($file, [
                    $invoice->invoice_number,
                    $invoice->client->name,
                    $invoice->client->email,
                    $invoice->issue_date,
                    $invoice->due_date,
                    ucfirst($invoice->status),
                    $invoice->total,
                    $invoice->amount_paid,
                    round($invoice->total - $invoice->amount_paid, 2),
                    $invoice->payment_mode ?: '—'
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, 'invoices_' . date('Y-m-d') . '.csv', $headers);
    }
};
?>

<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-extrabold text-stone-950 font-display tracking-tight">Invoices</h1>
        @if(auth()->user()->role !== 'client')
        <div class="flex space-x-3">
            <button wire:click="exportCsv" class="inline-flex items-center px-4 py-2.5 bg-stone-200 text-stone-800 hover:bg-stone-300 rounded-xl font-bold text-xs uppercase tracking-widest active:bg-stone-400 shadow-sm hover:shadow transition duration-150 cursor-pointer">
                Export CSV
            </button>
            <a href="{{ route('invoices.create') }}" class="inline-flex items-center px-4 py-2.5 bg-amber-600 text-stone-950 rounded-xl font-bold text-xs uppercase tracking-widest hover:bg-amber-700 active:bg-amber-800 shadow-sm hover:shadow transition duration-150">
                Create Invoice
            </a>
        </div>
        @endif
    </div>

    @if (session()->has('message'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-lg relative" role="alert">
            <span class="block sm:inline">{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex items-center">
        <select wire:model.live="statusFilter" class="rounded-lg border-stone-300 shadow-sm focus:border-teal-500 focus:ring focus:ring-teal-200 focus:ring-opacity-50 w-full sm:w-1/4 text-sm">
            <option value="">All Statuses</option>
            <option value="draft">Draft</option>
            <option value="sent">Sent</option>
            <option value="partially_paid">Partially Paid</option>
            <option value="paid">Paid</option>
            <option value="overdue">Overdue</option>
        </select>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-xl border border-stone-200">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-stone-200">
                <thead class="bg-stone-50">
                    <tr>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Invoice Number</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Client</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Issue Date</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Due Date</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Total</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Amount Paid</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Pending / Balance</th>
                        <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-stone-500 uppercase tracking-wider">Payment Mode</th>
                        <th scope="col" class="relative px-6 py-4">
                            <span class="sr-only">Actions</span>
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-stone-200">
                    @forelse($this->invoices() as $invoice)
                        <tr class="hover:bg-stone-50/50 transition">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-stone-900">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-teal-700 hover:text-teal-900">{{ $invoice->invoice_number }}</a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 font-semibold">{{ $invoice->client->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-500">{{ $invoice->issue_date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-500">{{ $invoice->due_date }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                @php
                                    $badgeClass = match ($invoice->status) {
                                        'draft' => 'bg-stone-500 text-white',
                                        'sent' => 'bg-sky-600 text-white',
                                        'partially_paid' => 'bg-orange-500 text-white',
                                        'paid' => 'bg-emerald-600 text-white',
                                        'overdue' => 'bg-rose-600 text-white',
                                        default => 'bg-stone-500 text-white',
                                    };
                                @endphp
                                <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full shadow-sm {{ $badgeClass }}">
                                    {{ str_replace('_', ' ', ucfirst($invoice->status)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-950 font-bold">{{ \App\Models\Invoice::formatINR($invoice->total) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 font-semibold">{{ \App\Models\Invoice::formatINR($invoice->amount_paid) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 font-semibold">
                                @if($invoice->status === 'paid' || ($invoice->total - $invoice->amount_paid) <= 0)
                                    —
                                @else
                                    {{ \App\Models\Invoice::formatINR($invoice->total - $invoice->amount_paid) }}
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-stone-600 font-semibold">
                                @if($invoice->status === 'paid' && $invoice->payment_mode)
                                    {{ $invoice->payment_mode }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-semibold space-x-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="text-stone-600 hover:text-stone-950">View</a>
                                @if(auth()->user()->role !== 'client')
                                    <a href="{{ route('invoices.edit', $invoice) }}" class="text-teal-700 hover:text-teal-900">Edit</a>
                                    <button wire:click="delete({{ $invoice->id }})" wire:confirm="Are you sure you want to delete this invoice?" class="text-rose-600 hover:text-rose-800 bg-none border-none p-0 cursor-pointer">Delete</button>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="px-6 py-8 whitespace-nowrap text-sm text-stone-500 text-center">No invoices found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>