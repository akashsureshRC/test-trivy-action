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
        Schema::create('custom_incomes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('input_type', ['fixed_amount','hourly_rate_factor_hours','custom_rate_quantity','monthly']);
            $table->boolean('taxed_annually')->default(false);
            $table->boolean('include_in_fluctuating_leave_rate')->default(false);
            $table->boolean('overtime')->default(false);
            $table->boolean('affects_wage_eti')->default(false);
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('rate_factor', 10, 2)->nullable();
            $table->decimal('employee_work_factor', 10, 2)->nullable();
            $table->decimal('hours_work_factor', 10, 2)->nullable();
            $table->decimal('custom_rate', 10, 2)->nullable();
            $table->decimal('percentage_income', 5, 2)->nullable();
            $table->longText('selected_income_items')->nullable();
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_incomes');
    }
};
