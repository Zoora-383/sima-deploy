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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('category_id')->constrained('asset_categories', 'id')->cascadeOnDelete();
            $table->string('code_asset')->unique();
            $table->string('name_asset');
            $table->string('image_url');
            $table->integer('masa_pakai_bulan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
