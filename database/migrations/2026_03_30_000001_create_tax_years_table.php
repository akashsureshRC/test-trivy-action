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
        Schema::create('tax_years', function (Blueprint $table) {
            $table->id();
            $table->string('label');                // e.g. "2025/2026"
            $table->date('effective_from');          // e.g. 2025-03-01
            $table->date('effective_to');            // e.g. 2026-02-28

            // PAYE Tax Brackets (JSON array of {min, max, base_tax, rate, threshold})
            $table->json('tax_brackets');

            // Age-based rebates
            $table->decimal('primary_rebate', 10, 2);
            $table->decimal('secondary_rebate', 10, 2);
            $table->decimal('tertiary_rebate', 10, 2);
            $table->unsignedInteger('secondary_rebate_age')->default(65);
            $table->unsignedInteger('tertiary_rebate_age')->default(75);

            // UIF
            $table->decimal('uif_rate', 5, 4)->default(0.0100);       // 1%
            $table->decimal('uif_ceiling', 10, 2)->default(177.12);    // Monthly cap

            // SDL
            $table->decimal('sdl_rate', 5, 4)->default(0.0100);       // 1%

            // ETI (Employment Tax Incentive)
            $table->unsignedInteger('eti_min_age')->default(18);
            $table->unsignedInteger('eti_max_age')->default(29);
            $table->decimal('eti_salary_cap', 10, 2)->default(6500.00);
            $table->decimal('eti_max_amount', 10, 2)->default(1000.00);
            $table->decimal('eti_rate', 5, 4)->default(0.5000);       // 50%

            // Overtime
            $table->decimal('ot_multiplier', 3, 2)->default(1.50);

            // Benefit tax rates
            $table->decimal('medical_aid_tax_rate', 5, 4)->default(0.1000);     // 10%
            $table->decimal('travel_allowance_tax_rate', 5, 4)->default(0.1000); // 10%

            // Lock mechanism
            $table->boolean('is_locked')->default(false);
            $table->foreignId('locked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tax_years');
    }
};
