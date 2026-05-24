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
        Schema::create('add_garnishees', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->string('bank');
            $table->string('account_number', 16)->unique();
            $table->string('branch_code', 6);
            $table->string('account_type');
            $table->boolean('include_eftexport')->default(false);
            $table->string('eft_payment_type')->nullable();
            $table->string('your_reference')->nullable();
            $table->string('beneficiary_reference')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_garnishees');
    }
};
