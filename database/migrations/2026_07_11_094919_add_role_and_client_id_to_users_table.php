<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'user', 'client'])->default('user')->after('password');
            $table->foreignId('client_id')->nullable()->after('role')->constrained('clients')->nullOnDelete();
        });

        // Seed the first user as admin if they exist
        DB::table('users')->where('email', 'test@example.com')->update([
            'role' => 'admin'
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropColumn(['role', 'client_id']);
        });
    }
};
