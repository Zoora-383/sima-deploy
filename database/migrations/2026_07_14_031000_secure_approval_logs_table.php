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
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->json('actor_snapshot')->nullable();
            $table->json('data_snapshot')->nullable();
            $table->index(['approvable_type', 'approvable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('approval_logs', function (Blueprint $table) {
            $table->dropIndex(['approvable_type', 'approvable_id']);
            $table->dropColumn(['actor_snapshot', 'data_snapshot']);
        });
    }
};
