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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id')->unique();
            $table->string('term')->nullable();
            $table->decimal('basic_salary', 10, 2)->default(0.00);
            $table->decimal('payout_amount', 10, 2)->nullable();
            $table->decimal('total_income', 10, 2)->nullable()->default(0.00);
            $table->decimal('uif_amount', 10, 2)->default(0.00);
            $table->decimal('tax_pay', 10, 2)->default(0.00);
            $table->decimal('net_pay', 10, 2)->default(0.00);
            $table->decimal('union_membership_fee', 10, 2)->default(0.00);
            $table->string('directive_number')->nullable();
            $table->string('directive_type')->nullable();
            $table->decimal('total_deductions', 10, 2)->nullable()->default(0.00);
            $table->decimal('voluntary_tax_over_deduction', 10, 2)->default(0.00);
            $table->decimal('total_medical_aid', 10, 2)->default(0.00);
            $table->decimal('medical_aid', 10, 2)->default(0.00);
            $table->decimal('travel_allowance', 10, 2)->nullable();
            $table->decimal('accommodation_benefits', 10, 2)->nullable()->default(0.00);
            $table->decimal('loss_of_income_policy_payout', 10, 2)->default(0.00);
            $table->decimal('taxable_portion', 10, 2)->default(0.00);
            $table->decimal('exempt_portion', 10, 2)->default(0.00);
            $table->string('bursary_type')->nullable();
            $table->boolean('employee_handles_payment')->default(false);
            $table->boolean('to_disabled_person')->default(false);
            $table->decimal('deemed_value', 10, 2)->nullable();
            $table->unsignedBigInteger('taxable_percentage_id')->nullable();
            $table->boolean('includes_maintenance_plan')->nullable()->default(false);
            $table->decimal('company_car_taxable_amount', 10, 2)->default(0.00);
            $table->decimal('company_car_totall_amount', 10, 2)->default(0.00);
            $table->decimal('company_car_under_operating_amount', 10, 2)->nullable();
            $table->decimal('company_car_taxable_percentage', 5, 2)->nullable();
            $table->decimal('company_car_total_amount', 10, 2)->default(0.00);
            $table->string('beneficiary_name')->nullable();
            $table->decimal('installment', 10, 2)->nullable();
            $table->decimal('income_protection_paid_by_employee', 10, 2)->nullable();
            $table->decimal('income_protection_paid_by_employer', 10, 2)->nullable();
            $table->boolean('income_protection_ownership')->nullable();
            $table->string('income_protection_deducted_from_employee')->nullable();
            $table->string('maintenance_order_installment')->nullable();
            $table->decimal('employer_contribution', 10, 2)->default(0.00);
            $table->decimal('employee_payment', 10, 2)->default(0.00);
            $table->boolean('apply_tax_credits')->default(false);
            $table->integer('members')->default(0);
            $table->decimal('employer_loan', 10, 2)->nullable()->default(0.00);
            $table->decimal('interest_benefit_amount', 10, 2)->nullable();
            $table->decimal('regular_deduction', 10, 2)->default(0.00);
            $table->decimal('taxable_deemed_value_of_vehicle', 10, 2)->nullable();
            $table->decimal('taxable_percentage', 5, 2)->nullable();
            $table->bigInteger('tax_directive_id')->nullable();
            $table->string('directive_income_source_code')->nullable();
            $table->string('directive_income_amount')->nullable();
            $table->string('amount_of_tax_to_deduct')->nullable();
            $table->string('directive_issue_date')->nullable();
            $table->string('percentage')->nullable();
            $table->boolean('purchase_price_includes_maintenance_plan')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
