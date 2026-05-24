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
        Schema::create('provident_fund_payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->string('contribution');
            $table->decimal('fixed_contribution_employee', 10, 2)->nullable();
            $table->decimal('fixed_contribution_employer', 10, 2)->nullable();
            $table->decimal('percentage_rfi_employee', 5, 2)->nullable();
            $table->decimal('percentage_rfi_employer', 5, 2)->nullable();
            $table->integer('category')->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('provident_fund_payrolls');
    }
};
