<?php

use Livewire\Component;
use App\Models\Invoice;

new class extends Component
{
    public function getStats()
    {
        $totalRevenue = Invoice::sum('amount_paid');
        
        // Sum of (total - amount_paid) across all unpaid / partially paid invoices
        $totalOutstanding = Invoice::where('status', '!=', 'paid')
            ->get()
            ->sum(function ($invoice) {
                return floatval($invoice->total) - floatval($invoice->amount_paid);
            });
        
        $overdueCount = Invoice::where('status', '!=', 'paid')
            ->where('due_date', '<', now()->format('Y-m-d'))
            ->count();

        return [
            'revenue' => floatval($totalRevenue),
            'outstanding' => floatval($totalOutstanding),
            'overdue_count' => $overdueCount,
        ];
    }

    public function recentInvoices()
    {
        return Invoice::with('client')
            ->latest()
            ->limit(5)
            ->get();
    }

    public function getChartData()
    {
        $labels = [];
        $data = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $labels[] = $date->format('M Y');

            $start = $date->copy()->startOfMonth()->format('Y-m-d');
            $end = $date->copy()->endOfMonth()->format('Y-m-d');

            $revenue = \App\Models\InvoicePayment::whereBetween('paid_on', [$start, $end])
                ->sum('amount');

            $data[] = floatval($revenue);
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
};
?>

<div class="space-y-8">
    <!-- Header -->
    <div class="bg-gradient-to-r from-teal-800 to-teal-700 rounded-2xl p-6 text-white shadow-md border border-teal-900/10">
        <h1 class="text-3xl font-extrabold tracking-tight font-display mb-2">Welcome to InvoiceApp</h1>
        <p class="text-teal-100/90 max-w-xl">Manage your clients, track payments, generate PDFs, and review your business metrics from one dashboard.</p>
    </div>

    <!-- Stats Grid -->
    @php
        $stats = $this->getStats();
    @endphp
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Revenue Card -->
        <div class="bg-white p-6 rounded-xl border border-stone-200 shadow-sm hover:shadow-md transition duration-200 flex items-center space-x-4">
            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-700">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-stone-500 uppercase tracking-wider block">Total Revenue</span>
                <span class="text-3xl font-extrabold text-stone-900 font-display">{{ \App\Models\Invoice::formatINR($stats['revenue']) }}</span>
            </div>
        </div>
 
        <!-- Outstanding Card -->
        <div class="bg-white p-6 rounded-xl border border-stone-200 shadow-sm hover:shadow-md transition duration-200 flex items-center space-x-4">
            <div class="p-3 bg-amber-50 rounded-lg text-amber-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-stone-500 uppercase tracking-wider block">Total Outstanding</span>
                <span class="text-3xl font-extrabold text-stone-900 font-display">{{ \App\Models\Invoice::formatINR($stats['outstanding']) }}</span>
            </div>
        </div>

        <!-- Overdue Card -->
        <div class="bg-white p-6 rounded-xl border border-stone-200 shadow-sm hover:shadow-md transition duration-200 flex items-center space-x-4">
            <div class="p-3 bg-red-50 rounded-lg text-red-600">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></svg>
            </div>
            <div>
                <span class="text-sm font-semibold text-stone-500 uppercase tracking-wider block">Overdue Invoices</span>
                <span class="text-3xl font-extrabold text-stone-900 font-display">{{ $stats['overdue_count'] }}</span>
            </div>
        </div>
    </div>

    <!-- Chart.js CDN -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Revenue Chart Section -->
    <div x-data="{
        labels: @js($this->getChartData()['labels']),
        data: @js($this->getChartData()['data']),
        init() {
            const ctx = this.$refs.canvas.getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: this.labels,
                    datasets: [{
                        label: 'Revenue (INR)',
                        data: this.data,
                        backgroundColor: '#d97706',
                        borderRadius: 6,
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                color: '#e5e7eb'
                            },
                            ticks: {
                                font: {
                                    family: 'Instrument Sans, sans-serif'
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    family: 'Instrument Sans, sans-serif'
                                }
                            }
                        }
                    }
                }
            });
        }
    }" class="bg-white rounded-xl border border-stone-200 p-6 shadow-sm">
        <h2 class="text-lg font-bold text-stone-950 font-display mb-4">Revenue Trend (Last 6 Months)</h2>
        <div class="h-64 relative">
            <canvas x-ref="canvas"></canvas>
        </div>
    </div>

    <!-- Recent Invoices List -->
    <div class="bg-white rounded-xl border border-stone-200 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex justify-between items-center">
            <h2 class="text-lg font-bold text-stone-950 font-display">Recent Invoices</h2>
            <a href="{{ route('invoices.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-900 transition">View All Invoices &rarr;</a>
        </div>
        <div class="divide-y divide-stone-100">
            @forelse($this->recentInvoices() as $invoice)
                <div class="px-6 py-4 flex items-center justify-between hover:bg-stone-50/50 transition">
                    <div class="flex items-center space-x-4">
                        <!-- Invoice Initials Avatar (Initials of Client) -->
                        @php
                            $colors = ['bg-amber-600', 'bg-teal-700', 'bg-emerald-600', 'bg-sky-600', 'bg-indigo-600', 'bg-rose-600', 'bg-purple-600'];
                            $colorIndex = crc32($invoice->client->name) % count($colors);
                            $avatarColor = $colors[abs($colorIndex)];
                        @endphp
                        <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm text-white {{ $avatarColor }}">
                            {{ strtoupper(substr($invoice->client->name, 0, 2)) }}
                        </div>
                        <div>
                            <a href="{{ route('invoices.show', $invoice) }}" class="font-bold text-stone-900 hover:text-teal-700 transition">
                                {{ $invoice->invoice_number }}
                            </a>
                            <span class="text-xs text-stone-500 block">Billed to {{ $invoice->client->name }}</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-6">
                        <span class="font-bold text-stone-950">{{ \App\Models\Invoice::formatINR($invoice->total) }}</span>
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
                        <span class="px-3 py-1 text-xs font-bold rounded-full {{ $badgeClass }}">
                            {{ str_replace('_', ' ', ucfirst($invoice->status)) }}
                        </span>
                    </div>
                </div>
            @empty
                <div class="px-6 py-12 text-center text-stone-500 text-sm">
                    No invoices created yet. <a href="{{ route('invoices.create') }}" class="text-teal-700 font-bold hover:underline">Create your first invoice</a>.
                </div>
            @endforelse
        </div>
    </div>
</div>