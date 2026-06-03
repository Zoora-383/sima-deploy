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
        Schema::table('items', function (Blueprint $table) {
            $table->enum('status', ['draft', 'pending', 'revision', 'active', 'maintenance', 'disposed'])->default('draft')->change();
        });

        Schema::table('maintenance_request', function (Blueprint $table) {
            $table->enum('status', ['pending_kasi', 'pending_pust', 'revision', 'in_progress', 'done', 'rejected'])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->enum('status', ['draft', 'active', 'maintenance', 'disposed'])->default('draft')->change();
        });

        Schema::table('maintenance_request', function (Blueprint $table) {
            $table->enum('status', ['pending_kasi', 'pending_pust', 'in_progress', 'done', 'rejected'])->change();
        });
    }
};
