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
        // For MySQL, we need to modify the enum to include 'likert'
        // First, drop the index on question_type
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_type']);
        });

        // Now modify the column
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('text', 'textarea', 'radio', 'checkbox', 'dropdown', 'rating', 'date', 'file', 'matrix', 'likert')");

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
        // For MySQL, to remove 'likert' from the enum values
        // First, drop the index on question_type
        Schema::table('questions', function (Blueprint $table) {
            $table->dropIndex(['question_type']);
        });

        // Now modify the column back
        DB::statement("ALTER TABLE questions MODIFY COLUMN question_type ENUM('text', 'textarea', 'radio', 'checkbox', 'dropdown', 'rating', 'date', 'file', 'matrix')");

        // Recreate the index
        Schema::table('questions', function (Blueprint $table) {
            $table->index('question_type');
        });
    }
};
