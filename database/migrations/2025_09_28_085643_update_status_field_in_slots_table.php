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
        Schema::table('slots', function (Blueprint $table) {
            //

            $table->string('type')->nullable();
            $table->string('active_time')->nullable();
            $table->enum('status', ['scheduled', 'active', 'started', 'completed', 'missed', 'rescheduled', 'leaved'])->default('scheduled')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slots', function (Blueprint $table) {
            //
        });
    }
};
