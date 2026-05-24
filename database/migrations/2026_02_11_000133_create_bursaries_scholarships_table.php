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
        Schema::create('bursaries_scholarships', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->decimal('taxable_portion', 10, 2)->nullable();
            $table->decimal('exempt_portion', 10, 2)->nullable();
            $table->string('bursary_type');
            $table->string('employee_handles_payment')->default(0);
            $table->string('to_disabled_person')->default('No');
            $table->timestamps();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bursaries_scholarships');
    }
};
