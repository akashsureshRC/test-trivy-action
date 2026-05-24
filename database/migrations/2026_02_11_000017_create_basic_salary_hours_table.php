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
        Schema::create('basic_salary_hours', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id');
            $table->string('term');
            $table->string('normal_hours');
            $table->string('ot_hours');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('basic_salary_hours');
    }
};
