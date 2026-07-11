<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Livewire\Livewire;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\CompanySetting;

class InvoiceAppTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_renders_dashboard(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Welcome to InvoiceApp');
    }

    public function test_can_create_and_edit_client(): void
    {
        Livewire::test('client-form')
            ->set('name', 'John Doe')
            ->set('email', 'john@example.com')
            ->set('phone', '123-456-7890')
            ->set('address', '123 Main St')
            ->call('save')
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St',
        ]);

        $client = Client::first();

        Livewire::test('client-form', ['client' => $client])
            ->assertSet('name', 'John Doe')
            ->set('name', 'John Updated')
            ->call('save')
            ->assertRedirect(route('clients.index'));

        $this->assertDatabaseHas('clients', [
            'id' => $client->id,
            'name' => 'John Updated',
        ]);
    }

    public function test_can_search_and_delete_client(): void
    {
        $client1 = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $client2 = Client::create([
            'name' => 'Jane Smith',
            'email' => 'jane@example.com',
        ]);

        Livewire::test('client-index')
            ->set('search', 'John')
            ->assertSee('John Doe')
            ->assertDontSee('Jane Smith')
            ->call('delete', $client1->id);

        $this->assertDatabaseMissing('clients', ['id' => $client1->id]);
        $this->assertDatabaseHas('clients', ['id' => $client2->id]);
    }

    public function test_can_create_invoice_with_items_tax_discount_recalculates_totals(): void
    {
        $client = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        Livewire::test('invoice-form')
            ->set('client_id', $client->id)
            ->set('invoice_number', 'INV-2026-0001')
            ->set('items.0.description', 'Web Development')
            ->set('items.0.quantity', 2)
            ->set('items.0.unit_price', 500) // Subtotal: 1000
            ->set('discount', 10) // 10% off -> Taxable: 900
            ->set('tax_rate', 20) // 20% tax on 900 -> 180 -> Total: 1080
            ->assertSet('total', 1080.00)
            ->call('save')
            ->assertRedirect(route('invoices.index'));

        $this->assertDatabaseHas('invoices', [
            'invoice_number' => 'INV-2026-0001',
            'total' => 1080.00,
            'discount' => 10.00,
            'tax_rate' => 20.00,
            'client_id' => $client->id,
        ]);

        $invoice = Invoice::where('invoice_number', 'INV-2026-0001')->first();
        $this->assertCount(1, $invoice->items);
        $this->assertEquals(1000.00, $invoice->items[0]->line_total);
    }

    public function test_can_save_company_settings(): void
    {
        Livewire::test('settings-form')
            ->set('company_name', 'TechCorp Ltd')
            ->set('email', 'billing@techcorp.com')
            ->set('phone', '555-0199')
            ->set('address', '100 Innovation Way')
            ->set('tax_id', 'TX-987654')
            ->call('save')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('company_settings', [
            'company_name' => 'TechCorp Ltd',
            'email' => 'billing@techcorp.com',
            'tax_id' => 'TX-987654',
        ]);
    }

    public function test_can_mark_invoice_as_paid(): void
    {
        $client = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-PAY-01',
            'issue_date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'status' => 'sent',
            'total' => 250.00,
        ]);

        Livewire::test('invoice-show', ['invoice' => $invoice])
            ->set('paymentType', 'full')
            ->set('paymentMode', 'UPI')
            ->set('paidOn', now()->format('Y-m-d'))
            ->call('recordPayment')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'status' => 'paid',
            'amount_paid' => 250.00,
            'paid_date' => now()->format('Y-m-d'),
        ]);
    }

    public function test_can_record_partial_payments(): void
    {
        $client = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-PAY-PARTIAL',
            'issue_date' => '2026-07-01',
            'due_date' => '2026-07-31',
            'status' => 'sent',
            'total' => 1000.00,
        ]);

        // First partial payment: 300 via UPI
        Livewire::test('invoice-show', ['invoice' => $invoice])
            ->set('paymentType', 'partial')
            ->set('paymentAmount', 300)
            ->set('paymentMode', 'UPI')
            ->set('paidOn', '2026-07-05')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertEquals('partially_paid', $invoice->status);
        $this->assertEquals(300.00, $invoice->amount_paid);
        $this->assertCount(1, $invoice->payments);

        // Second partial payment: 700 via Bank Transfer (completing the payment)
        Livewire::test('invoice-show', ['invoice' => $invoice])
            ->set('paymentType', 'partial')
            ->set('paymentAmount', 700)
            ->set('paymentMode', 'Bank Transfer')
            ->set('paidOn', '2026-07-10')
            ->call('recordPayment')
            ->assertHasNoErrors();

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
        $this->assertEquals(1000.00, $invoice->amount_paid);
        $this->assertEquals(now()->format('Y-m-d'), $invoice->paid_date);
        $this->assertCount(2, $invoice->payments);
    }

    public function test_dashboard_renders_stats(): void
    {
        $client = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        // Paid Invoice -> Revenue: 500
        $invoice1 = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-DASH-01',
            'issue_date' => '2026-07-01',
            'due_date' => '2026-07-15',
            'status' => 'paid',
            'total' => 500.00,
            'amount_paid' => 500.00,
            'paid_date' => '2026-07-05',
        ]);
        $invoice1->payments()->create(['amount' => 500.00, 'payment_mode' => 'UPI', 'paid_on' => '2026-07-05']);

        // Partially Paid Invoice -> Revenue: +100, Outstanding: +(400 - 100) = +300
        $invoice2 = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-DASH-02',
            'issue_date' => '2026-07-01',
            'due_date' => '2026-08-15',
            'status' => 'partially_paid',
            'total' => 400.00,
            'amount_paid' => 100.00,
        ]);
        $invoice2->payments()->create(['amount' => 100.00, 'payment_mode' => 'Cash', 'paid_on' => '2026-07-06']);

        // Overdue Invoice -> Overdue count: 1, Outstanding: +200
        Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-DASH-03',
            'issue_date' => '2026-06-01',
            'due_date' => '2026-06-30', // passed
            'status' => 'sent',
            'total' => 200.00,
        ]);

        // Total Revenue = 500 + 100 = 600
        // Total Outstanding = 300 (from invoice2) + 200 (from invoice3) = 500
        Livewire::test('dashboard')
            ->assertSee('₹600.00') // Revenue formatted in INR
            ->assertSee('₹500.00') // Outstanding formatted in INR
            ->assertSee('1'); // Overdue count
    }

    public function test_pdf_download_route(): void
    {
        $client = Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $invoice = Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-PDF-99',
            'issue_date' => '2026-07-10',
            'due_date' => '2026-08-10',
            'status' => 'draft',
            'total' => 100.00,
        ]);

        $pdfResponse = $this->get("/invoices/{$invoice->id}/pdf");
        $pdfResponse->assertStatus(200);
        $pdfResponse->assertHeader('content-type', 'application/pdf');
    }
}
