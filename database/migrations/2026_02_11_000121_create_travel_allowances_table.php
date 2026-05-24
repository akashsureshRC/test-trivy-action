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
        Schema::create('travel_allowances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->boolean('fixed_allowance')->default(false);
            $table->decimal('fixed_amount', 10, 2)->nullable();
            $table->boolean('reimbursed_expenses')->default(false);
            $table->boolean('company_petrol_card')->default(false);
            $table->boolean('reimbursed_per_km')->default(false);
            $table->decimal('rate_per_km', 10, 2)->nullable();
            $table->boolean('subject_to_20_tax')->default(false);
            $table->enum('status', ['Active','Inactive'])->default('Active');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('travel_allowances');
    }
};
