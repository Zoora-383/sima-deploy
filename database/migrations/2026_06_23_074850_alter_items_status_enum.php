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
        // 1. Update any existing records with 'pending' to 'pending_kasi' first so they don't break the new enum
        DB::table('items')->where('status', 'pending')->update(['status' => 'pending_kasi']);

        // 2. Modify column
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('draft', 'pending_kasi', 'pending_pust', 'revision', 'active', 'maintenance', 'disposed') NOT NULL DEFAULT 'draft'");
        } else {
            Schema::table('items', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE items MODIFY COLUMN status ENUM('draft', 'pending', 'revision', 'active', 'maintenance', 'disposed') NOT NULL DEFAULT 'draft'");
        } else {
            Schema::table('items', function (Blueprint $table) {
                $table->string('status')->default('draft')->change();
            });
        }
    }
};
