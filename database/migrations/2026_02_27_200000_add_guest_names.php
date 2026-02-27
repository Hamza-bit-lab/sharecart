<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->string('completed_by_name', 100)->nullable()->after('completed_at');
        });

        Schema::table('list_guest_tokens', function (Blueprint $table) {
            $table->string('display_name', 100)->nullable()->after('expires_at');
        });
    }

    public function down(): void
    {
        Schema::table('list_items', function (Blueprint $table) {
            $table->dropColumn('completed_by_name');
        });

        Schema::table('list_guest_tokens', function (Blueprint $table) {
            $table->dropColumn('display_name');
        });
    }
};

