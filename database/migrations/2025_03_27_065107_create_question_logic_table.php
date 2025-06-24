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
        Schema::create('question_logic', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->enum('condition_type', ['equals', 'not_equals', 'contains', 'not_contains', 'greater_than', 'less_than']);
            $table->text('condition_value');
            $table->enum('action_type', ['show', 'hide', 'jump']);
            $table->unsignedBigInteger('action_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_logic');
    }
}; 