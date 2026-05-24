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
        Schema::create('medical_aids', function (Blueprint $table) {
            $table->id();
            $table->decimal('total_amount', 10, 2);
            $table->decimal('employer_contribution', 10, 2)->nullable();
            $table->boolean('employee_payment')->default(false);
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->unsignedBigInteger('payroll_id')->nullable();
            $table->boolean('apply_tax_credits')->default(false);
            $table->integer('members')->default(1);
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medical_aids');
    }
};
