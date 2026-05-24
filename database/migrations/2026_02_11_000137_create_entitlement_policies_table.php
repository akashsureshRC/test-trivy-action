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
        Schema::create('entitlement_policies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('leave_management_id');
            $table->boolean('use_custom_name')->default(false);
            $table->string('custom_name')->nullable();
            $table->boolean('use_hours_worked')->default(false);
            $table->decimal('hours_per_leave', 8, 2)->nullable();
            $table->boolean('paid_leave_contributes')->default(false);
            $table->decimal('default_entitlement', 8, 2)->default(0.00);
            $table->integer('entitlement_after_months')->nullable();
            $table->boolean('use_upfront_accrual')->default(false);
            $table->boolean('allow_carry_forward')->default(false);
            $table->integer('carry_forward_expiry_months')->nullable();
            $table->string('limit_type')->nullable();
            $table->decimal('limit_value', 8, 2)->nullable();
            $table->text('cycle_specific_rules')->nullable();
            $table->timestamps();
            $table->foreign('leave_management_id')->references('id')->on('leave_managements')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entitlement_policies');
    }
};
