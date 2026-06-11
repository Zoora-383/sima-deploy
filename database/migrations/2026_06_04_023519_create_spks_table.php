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
        Schema::create('spks', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('maintenance_id')->constrained('maintenance_requests', 'id')->cascadeOnDelete();
            $table->string('nomor_spk')->unique();
            $table->date('tanggal_mulai_efektif')->nullable();
            $table->date('tanggal_selesai_target')->nullable();
            $table->decimal('pagu_anggaran_disetujui', 15, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spks');
    }
};
