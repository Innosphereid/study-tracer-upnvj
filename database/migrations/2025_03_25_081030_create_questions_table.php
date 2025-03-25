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
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->enum('question_type', [
                'text', 'textarea', 'radio', 'checkbox', 
                'dropdown', 'rating', 'date', 'file', 
                'matrix', 'static'
            ]);
            $table->string('title');
            $table->text('description')->nullable();
            $table->boolean('is_required')->default(false);
            $table->integer('order')->default(0);
            $table->json('settings')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('questions');
    }
};