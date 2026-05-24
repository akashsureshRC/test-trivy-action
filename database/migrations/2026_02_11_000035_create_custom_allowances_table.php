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
        Schema::create('custom_allowances', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('input_type');
            $table->boolean('affects wage for eti purpose')->default(false);
            $table->boolean('enable_pro_rata')->nullable();
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('rate_factor', 10, 2)->nullable();
            $table->decimal('employee_work_factor', 10, 2)->nullable();
            $table->boolean('different_rate_for_every_employee')->nullable();
            $table->decimal('hours_work_factor', 10, 2)->nullable();
            $table->decimal('custom_rate', 10, 2)->nullable();
            $table->decimal('monthly_amount', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_allowances');
    }
};
