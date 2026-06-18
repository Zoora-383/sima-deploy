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
        Schema::create('maintenance_rekaps', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('spk_id')->constrained('spks', 'id')->cascadeOnDelete();
            $table->date('tanggal_selesai_aktual')->nullable();
            $table->enum('status', ['success', 'partial', 'failed']);
            $table->text('ringkasan_tindakan')->nullable();
            $table->decimal('realisasi_biaya', 10, 2)->nullable();
            $table->date('jadwal_preventif_berikutnya')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_rekaps');
    }
};
