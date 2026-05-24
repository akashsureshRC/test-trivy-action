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
        Schema::create('leave_managements', function (Blueprint $table) {
            $table->id();
            $table->string('leave_name');
            $table->integer('cycle_length');
            $table->string('cycle_start_type')->nullable();
            $table->date('custom_cycle_date')->nullable();
            $table->string('visible_for');
            $table->boolean('unpaid_leave')->default(false);
            $table->boolean('show_on_payslip')->default(false);
            $table->boolean('show_leave_expiry')->default(false);
            $table->boolean('set_min_balance_rule')->default(false);
            $table->decimal('minimum_balance', 10, 2)->nullable();
            $table->enum('allow_rule_override', ['not allowed','admins','approvers admin'])->nullable();
            $table->boolean('hide_balances')->default(false);
            $table->string('workspace_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_managements');
    }
};
