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
        Schema::create('company_basic_salaries', function (Blueprint $table) {
            $table->id();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->boolean('dont_auto_pay_holidays')->default(false);
            $table->boolean('enable_shifts')->default(false);
            $table->string('employee_minimum_pay')->nullable();
            $table->string('employee_fixed_component')->nullable();
            $table->string('work_minimum_pay')->nullable();
            $table->string('work_fixed_component')->nullable();
            $table->boolean('override_holiday_pay_rates')->default(false);
            $table->decimal('holiday_normal_multiplier', 5, 2)->default(2.00);
            $table->decimal('holiday_overtime_multiplier', 5, 2)->default(2.00);
            $table->string('minimum_pay')->nullable();
            $table->boolean('override_sunday_pay_rates')->default(false);
            $table->decimal('normally_works_multiplier', 5, 2)->default(1.50);
            $table->decimal('normally_off_multiplier', 5, 2)->default(2.00);
            $table->boolean('separate_overtime_hours')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_basic_salaries');
    }
};
