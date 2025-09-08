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
        Schema::create('teachers', function (Blueprint $table) {
            $table->id();


            $table->string('employee_id')->unique();
            $table->string('photo')->nullable();
            $table->string('full_name');
            $table->string('father_name')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->integer('age')->nullable();
            $table->string('email')->unique();
            $table->string('phone');
            $table->string('alternate_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->date('hire_date')->nullable();
            $table->string('username')->unique();
            $table->string('password');
            $table->timestamp('last_login')->nullable();
            $table->string('national_id')->nullable();
            $table->string('time_zone')->nullable();
            $table->string('other')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('teachers');
    }
};
