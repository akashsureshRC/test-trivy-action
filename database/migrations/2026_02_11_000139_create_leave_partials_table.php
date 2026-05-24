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
        Schema::create('leave_partials', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_record_id')->nullable();
            $table->date('date')->nullable();
            $table->decimal('hours', 4, 2)->nullable()->default(0.00);
            $table->timestamps();
            $table->foreign('leave_record_id')->references('id')->on('leave_records')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_partials');
    }
};
