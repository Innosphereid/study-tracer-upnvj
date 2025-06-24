<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // First, drop the index on question_type
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_type']);
        });

        // Now modify the column to include the new question types
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('text', 'textarea', 'radio', 'checkbox', 'dropdown', 'rating', 'date', 'file', 'matrix', 'likert', 'yes-no', 'slider', 'ranking')");

        // Recreate the index
        Schema::table('questions', function (Blueprint $table) {
            $table->index('question_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // First, drop the index on question_type
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_type']);
        });

        // Now modify the column back, removing the new question types
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('text', 'textarea', 'radio', 'checkbox', 'dropdown', 'rating', 'date', 'file', 'matrix', 'likert')");

        // Recreate the index
        Schema::table('questions', function (Blueprint $table) {
            $table->index('question_type');
        });
    }
};
