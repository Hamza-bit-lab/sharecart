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
        Schema::table('list_items', function (Blueprint $table) {
            $table->foreignId('claimed_by_user_id')->nullable()->after('completed_by_name')->constrained('users')->nullOnDelete();
            $table->string('claimed_by_name')->nullable()->after('claimed_by_user_id');
            $table->timestamp('claimed_at')->nullable()->after('claimed_by_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropForeign(['claimed_by_user_id']);
            $table->dropColumn(['claimed_by_user_id', 'claimed_by_name', 'claimed_at']);
        });
    }
};
