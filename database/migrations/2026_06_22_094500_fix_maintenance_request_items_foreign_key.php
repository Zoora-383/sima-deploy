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
        Schema::table('maintenance_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_request_items', 'maintenance_request_id')) {
                $table->renameColumn('maintenance_request_id', 'maintenance_id');
            } elseif (!Schema::hasColumn('maintenance_request_items', 'maintenance_id')) {
                $table->foreignId('maintenance_id')->constrained('maintenance_requests', 'id')->cascadeOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_request_items', 'maintenance_id')) {
                $table->renameColumn('maintenance_id', 'maintenance_request_id');
            }
        });
    }
};
