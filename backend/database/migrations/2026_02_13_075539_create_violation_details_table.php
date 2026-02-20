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
        Schema::create('violation_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('violation_id')->constrained()->onDelete('cascade');
            $table->foreignId('violation_type_id')->constrained('violation_types')->onDelete('cascade');
            $table->float('confidence_score')->nullable();
            $table->text('additional_info')->nullable();
            $table->enum('status', ['unverified', 'confirmed', 'dismissed'])->default('unverified');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('violation_details');
    }
};
