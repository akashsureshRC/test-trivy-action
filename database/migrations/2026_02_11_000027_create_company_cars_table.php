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
        Schema::create('company_cars', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term')->nullable();
            $table->decimal('deemed_value', 10, 2)->nullable();
            $table->string('includes_maintenance_plan')->default(0);
            $table->unsignedBigInteger('taxable_percentage_id');
            $table->timestamps();
            $table->index('employee_id');
            $table->index('taxable_percentage_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_cars');
    }
};
