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
        // 1. Add unique constraint to user_profiles.user_id
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->unique('user_id');
        });

        // 2. Add unique constraint to spks.maintenance_id
        Schema::table('spks', function (Blueprint $table) {
            $table->unique('maintenance_id');
        });

        // 3. Add unique constraint to maintenance_rekaps.spk_id
        Schema::table('maintenance_rekaps', function (Blueprint $table) {
            $table->unique('spk_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropUnique(['user_id']);
        });

        Schema::table('spks', function (Blueprint $table) {
            $table->dropUnique(['maintenance_id']);
        });

        Schema::table('maintenance_rekaps', function (Blueprint $table) {
            $table->dropUnique(['spk_id']);
        });
    }
};
