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
        Schema::create('tax_directives_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->string('directive_number');
            $table->string('tax_directive_id');
            $table->string('directive_income_source_code')->nullable();
            $table->decimal('directive_income_amount', 15, 2)->nullable();
            $table->decimal('amount_of_tax_to_deduct', 15, 2)->nullable();
            $table->date('directive_issue_date')->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_directives_entries');
    }
};
