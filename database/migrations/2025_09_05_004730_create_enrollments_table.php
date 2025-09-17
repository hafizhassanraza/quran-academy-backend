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
        Schema::create('enrollments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('student_id')->constrained('students')->onDelete('cascade');
            $table->foreignId('course_id')->constrained('courses')->onDelete('cascade');
            $table->foreignId('teacher_id')->constrained('teachers')->onDelete('cascade');

            $table->date('enrollment_date');
            $table->date('starting_date');
            $table->date('ending_date')->nullable();
            
            $table->json('slots'); // Store slots as simple string array


            $table->string('grade')->nullable();
            $table->year('year')->nullable();
            $table->string('other')->nullable();
            $table->enum('status', ['enrolled', 'completed', 'dropped'])->default('enrolled');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
