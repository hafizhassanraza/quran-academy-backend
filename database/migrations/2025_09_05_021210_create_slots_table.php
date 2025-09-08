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
        Schema::create('slots', function (Blueprint $table) {
            $table->id();

            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');
            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('chapter_id')->constrained('chapters')->onDelete('cascade');
            $table->integer('slot_number');
            $table->date('slot_date');
            $table->date('reschedule_date')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->string('other')->nullable();
            $table->enum('status', ['scheduled', 'started', 'completed', 'missed', 'rescheduled'])->default('scheduled');



            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slots');
    }
};
