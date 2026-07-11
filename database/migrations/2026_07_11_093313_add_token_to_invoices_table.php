<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('token')->nullable()->unique();
        });

        // Populate existing invoices with UUIDs
        $invoices = DB::table('invoices')->get();
        foreach ($invoices as $invoice) {
            DB::table('invoices')->where('id', $invoice->id)->update([
                'token' => (string) Str::uuid()
            ]);
        }

        // Change the column to be non-nullable
        Schema::table('invoices', function (Blueprint $table) {
            $table->uuid('token')->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
