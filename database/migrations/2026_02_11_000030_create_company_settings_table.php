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
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->enum('minimum_wage', ['Not required','Monthly Amount','Hourly Rate'])->default('Not required');
            $table->decimal('minimum_wage_monthly', 10, 2)->nullable();
            $table->decimal('minimum_wage_normal_rate', 10, 2)->nullable();
            $table->boolean('special_economic_zone')->default(false);
            $table->string('economic_zone')->nullable();
            $table->date('effective_from');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
