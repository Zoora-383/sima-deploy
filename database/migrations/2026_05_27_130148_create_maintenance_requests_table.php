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
        Schema::create('maintenance_requests', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('nomor_pengajuan');
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->foreignId('requester_id')->constrained('users', 'id')->cascadeOnDelete();
            $table->string('title');
            $table->enum('priority', ['high', 'medium', 'low']);
            $table->enum('type', ['korektif', 'preventif']);
            $table->text('description')->nullable();
            $table->integer('estimated_day')->nullable();
            $table->date('target_completion_expectations')->nullable();
            $table->decimal('total_estimated_cost', 10, 2)->nullable();
            $table->enum('status', ['draft', 'pending_kasi', 'pending_pust', 'in_progress', 'done', 'rejected']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
