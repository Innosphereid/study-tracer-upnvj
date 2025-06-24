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
        Schema::create('answer_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('response_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->foreignId('questionnaire_id')->constrained()->onDelete('cascade');
            $table->text('answer_value')->nullable();
            $table->json('answer_data')->nullable();
            $table->timestamps();

            // Add indexes
            $table->index(['response_id', 'question_id']);
            $table->index('questionnaire_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('answer_details');
    }
}; 