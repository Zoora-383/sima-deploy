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
        if (Schema::hasColumn('spks', 'request_id')) {
            Schema::table('spks', function (Blueprint $table) {
                // Try dropping the foreign key constraint safely
                try {
                    $table->dropForeign('spks_request_id_foreign');
                } catch (\Exception $e) {
                    // Ignore if constraint name is different or doesn't exist
                }

                // Rename the column from request_id to maintenance_id
                $table->renameColumn('request_id', 'maintenance_id');
            });

            Schema::table('spks', function (Blueprint $table) {
                // Add the correct foreign key constraint to maintenance_requests table
                $table->foreign('maintenance_id')
                      ->references('id')
                      ->on('maintenance_requests')
                      ->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('spks', 'maintenance_id')) {
            Schema::table('spks', function (Blueprint $table) {
                try {
                    $table->dropForeign('spks_maintenance_id_foreign');
                } catch (\Exception $e) {
                    // Ignore if constraint name is different or doesn't exist
                }

                $table->renameColumn('maintenance_id', 'request_id');
            });

            Schema::table('spks', function (Blueprint $table) {
                $table->foreign('request_id')
                      ->references('id')
                      ->on('users')
                      ->cascadeOnDelete();
            });
        }
    }
};
