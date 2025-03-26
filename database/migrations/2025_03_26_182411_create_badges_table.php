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
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image_path')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['student', 'mentor'])->default('student');
            $table->json('requirements')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create student_badge pivot table
        Schema::create('student_badge', function (Blueprint $table) {
            $table->foreignId('student_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->primary(['student_id', 'badge_id']);
            $table->timestamps();
        });

        // Create mentor_badge pivot table
        Schema::create('mentor_badge', function (Blueprint $table) {
            $table->foreignId('mentor_id')->constrained()->onDelete('cascade');
            $table->foreignId('badge_id')->constrained()->onDelete('cascade');
            $table->timestamp('earned_at');
            $table->primary(['mentor_id', 'badge_id']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mentor_badge');
        Schema::dropIfExists('student_badge');
        Schema::dropIfExists('badges');
    }
};