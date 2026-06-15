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
        Schema::create('maintenance_request_items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('maintenance_id')->constrained('maintenance_requests', 'id')->cascadeOnDelete();
            $table->string('nama_item');
            $table->string('image_item')->nullable();
            $table->integer('qty')->nullable();
            $table->string('satuan')->nullable();
            $table->decimal('estimasi_biaya_satuan', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_request_items');
    }
};
