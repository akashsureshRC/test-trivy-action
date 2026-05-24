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
        Schema::create('payslip_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workspace_id');
            $table->unsignedBigInteger('billing_cycle_id')->nullable();
            $table->unsignedBigInteger('payslip_id')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->string('salary_month', 7);
            $table->decimal('amount_charged', 10, 2)->default(0.00);
            $table->unsignedBigInteger('tier_id')->nullable();
            $table->integer('cumulative_count')->default(1);
            $table->enum('status', ['pending','invoiced','paid'])->default('pending');
            $table->timestamps();
            $table->index(['user_id', 'billing_cycle_id']);
            $table->index(['workspace_id', 'salary_month']);
            $table->index('status');
            $table->foreign('billing_cycle_id')->references('id')->on('billing_cycles')->onDelete('set null');
            $table->foreign('payslip_id')->references('id')->on('pay_slips')->onDelete('cascade');
            $table->foreign('tier_id')->references('id')->on('billing_tiers')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('workspace_id')->references('id')->on('work_spaces')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslip_usages');
    }
};
