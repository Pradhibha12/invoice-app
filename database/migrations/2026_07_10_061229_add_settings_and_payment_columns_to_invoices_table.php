<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('tax_rate', 5, 2)->default(0.00);
            $table->decimal('discount', 5, 2)->default(0.00);
            $table->date('paid_date')->nullable();
            $table->decimal('amount_paid', 10, 2)->default(0.00);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['tax_rate', 'discount', 'paid_date', 'amount_paid']);
        });
    }
};
