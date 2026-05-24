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
        Schema::create('basic_salaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->boolean('hourly_paid')->default(false);
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('dont_auto_pay_public_holidays')->default(false);
            $table->decimal('fixed_salary', 10, 2)->nullable()->default(0.00);
            $table->boolean('paid_for_additional_hours')->default(false);
            $table->boolean('override_hourly_rate')->default(false);
            $table->decimal('rate_override', 10, 2)->nullable();
            $table->bigInteger('normal_hours')->default(0);
            $table->bigInteger('ot_hours')->default(0);
            $table->string('term')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_salaries');
    }
};
