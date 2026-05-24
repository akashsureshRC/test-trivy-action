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
        Schema::create('termination_lumps', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term');
            $table->string('directive_number')->unique();
            $table->date('directive_issue_date');
            $table->string('directive_income_source_code');
            $table->decimal('amount_of_tax_to_deduct', 10, 2)->default(0.00);
            $table->decimal('directive_income_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('termination_lumps');
    }
};
