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
        Schema::create('job_logs', function (Blueprint $table) {
            $table->id();
            $table->string('job_name');
            $table->string('method_name');
            $table->string('status');
            $table->string('error_message')->nullable();
            $table->integer('retry_count')->default(0);
            $table->integer('priority')->default(0);        // Job priority (Low: 0, Medium: 1, High: 2)
            $table->integer('max_retries')->default(3);      // Max retries
            $table->integer('retry_delay')->default(5);      // Delay between retries (in seconds)
            $table->integer('timeout')->default(60);         // Job timeout (in seconds)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('job_logs');
    }
};
