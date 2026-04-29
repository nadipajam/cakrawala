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
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_detail_id')->constrained()->cascadeOnDelete();
            $table->string('qr_code_path')->nullable();
            $table->string('pdf_path')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->timestamps();

            $table->unique('booking_detail_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
