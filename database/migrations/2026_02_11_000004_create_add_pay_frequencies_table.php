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
        Schema::create('add_pay_frequencies', function (Blueprint $table) {
            $table->id();
            $table->string('pay_frequency');
            $table->string('last_day_of_period')->nullable();
            $table->date('biweekly_date')->nullable();
            $table->integer('last_day_of_month')->nullable();
            $table->boolean('go_further_back')->default(false);
            $table->string('years_back')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('add_pay_frequencies');
    }
};
