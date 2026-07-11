<?php

use Illuminate\Support\Facades\Route;

Route::livewire('/login', 'login')->name('login')->middleware('guest');

Route::middleware('auth')->group(function () {
    Route::livewire('/', 'dashboard')->name('dashboard');
    Route::livewire('/settings', 'settings-form')->name('settings.edit');

    Route::livewire('/clients', 'client-index')->name('clients.index');
    Route::livewire('/clients/create', 'client-form')->name('clients.create');
    Route::livewire('/clients/{client}/edit', 'client-form')->name('clients.edit');

    Route::livewire('/invoices', 'invoice-index')->name('invoices.index');
    Route::livewire('/invoices/create', 'invoice-form')->name('invoices.create');
    Route::livewire('/invoices/{invoice}/edit', 'invoice-form')->name('invoices.edit');
    Route::livewire('/invoices/{invoice}', 'invoice-show')->name('invoices.show');

    Route::get('/invoices/{invoice}/pdf', function (App\Models\Invoice $invoice) {
        $pdf = Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.invoice', [
            'invoice' => $invoice->load(['client', 'items', 'payments']),
            'company' => App\Models\CompanySetting::getOrNew(),
        ]);
        return $pdf->download($invoice->invoice_number . '.pdf');
    });

    Route::get('/invoices/{invoice}/print', function (App\Models\Invoice $invoice) {
        return view('invoices.print', [
            'invoice' => $invoice->load(['client', 'items', 'payments']),
            'company' => App\Models\CompanySetting::getOrNew(),
        ]);
    })->name('invoices.print');

    Route::post('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout');
});
