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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('item_categories', 'id')->cascadeOnDelete();
            $table->string('code_item')->unique();
            $table->string('name');
            $table->enum('type', ['logistic', 'non-logistic', 'service']);
            $table->enum('status', ['draft', 'pending', 'revision', 'active', 'maintenance', 'disposed'])->default('draft')->change();
            $table->unsignedInteger('units')->nullable();
            $table->string('image_item')->nullable();
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
