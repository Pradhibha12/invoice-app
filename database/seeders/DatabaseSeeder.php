<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $client = \App\Models\Client::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '123-456-7890',
            'address' => '123 Main St, Anytown',
        ]);

        $invoice = \App\Models\Invoice::create([
            'client_id' => $client->id,
            'invoice_number' => 'INV-1234',
            'issue_date' => '2026-07-10',
            'due_date' => '2026-08-10',
            'status' => 'draft',
            'total' => 150.00,
            'payment_mode' => 'UPI',
        ]);

        $invoice->items()->create([
            'description' => 'Test Service',
            'quantity' => 1,
            'unit_price' => 150.00,
            'line_total' => 150.00,
        ]);
    }
}
