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
        Schema::create('billing_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('min_payslips');
            $table->integer('max_payslips')->nullable();
            $table->decimal('price_per_payslip', 10, 2);
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('billing_tiers');
    }
};
