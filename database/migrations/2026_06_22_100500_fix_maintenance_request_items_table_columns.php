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
        // 1. Drop foreign keys and columns that might conflict
        Schema::table('maintenance_request_items', function (Blueprint $table) {
            if (Schema::hasColumn('maintenance_request_items', 'maintenance_request_id')) {
                try {
                    $table->dropForeign(['maintenance_request_id']);
                } catch (\Exception $e) {
                    // Ignore constraint if not found
                }
                $table->dropColumn('maintenance_request_id');
            }

            if (Schema::hasColumn('maintenance_request_items', 'image')) {
                $table->dropColumn('image');
            }

            if (Schema::hasColumn('maintenance_request_items', 'foto')) {
                $table->dropColumn('foto');
            }
        });

        // 2. Add correct columns with constraints
        Schema::table('maintenance_request_items', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_request_items', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'maintenance_id')) {
                $table->foreignId('maintenance_id')->nullable()->constrained('maintenance_requests', 'id')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'nama_item')) {
                $table->string('nama_item')->nullable();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'image_item')) {
                $table->string('image_item')->nullable();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'qty')) {
                $table->integer('qty')->nullable();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'satuan')) {
                $table->string('satuan')->nullable();
            }

            if (!Schema::hasColumn('maintenance_request_items', 'estimasi_biaya_satuan')) {
                $table->decimal('estimasi_biaya_satuan', 10, 2)->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Corrective migration for schema drift - rollback is not required.
    }
};
