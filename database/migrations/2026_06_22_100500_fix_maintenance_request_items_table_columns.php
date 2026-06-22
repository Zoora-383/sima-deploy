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
            // 1. Check & fix uuid
            if (!Schema::hasColumn('maintenance_request_items', 'uuid')) {
                $table->uuid('uuid')->nullable()->unique();
            }

            // 2. Check & fix maintenance_id
            if (Schema::hasColumn('maintenance_request_items', 'maintenance_request_id')) {
                $table->renameColumn('maintenance_request_id', 'maintenance_id');
            } elseif (!Schema::hasColumn('maintenance_request_items', 'maintenance_id')) {
                $table->foreignId('maintenance_id')->nullable()->constrained('maintenance_requests', 'id')->cascadeOnDelete();
            }

            // 3. Check & fix nama_item
            if (!Schema::hasColumn('maintenance_request_items', 'nama_item')) {
                $table->string('nama_item')->nullable();
            }

            // 4. Check & fix image_item
            if (Schema::hasColumn('maintenance_request_items', 'image')) {
                $table->renameColumn('image', 'image_item');
            } elseif (Schema::hasColumn('maintenance_request_items', 'foto')) {
                $table->renameColumn('foto', 'image_item');
            } elseif (!Schema::hasColumn('maintenance_request_items', 'image_item')) {
                $table->string('image_item')->nullable();
            }

            // 5. Check & fix qty
            if (!Schema::hasColumn('maintenance_request_items', 'qty')) {
                $table->integer('qty')->nullable();
            }

            // 6. Check & fix satuan
            if (!Schema::hasColumn('maintenance_request_items', 'satuan')) {
                $table->string('satuan')->nullable();
            }

            // 7. Check & fix estimasi_biaya_satuan
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
