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
        Schema::table('lists', function (Blueprint $table) {
            $table->timestamp('last_ping_at')->nullable();
            $table->foreignId('last_ping_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('last_ping_by_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lists', function (Blueprint $table) {
            $table->dropForeign(['last_ping_by_id']);
            $table->dropColumn(['last_ping_at', 'last_ping_by_id', 'last_ping_by_name']);
        });
    }
};
