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
        if (!Schema::hasColumn('employer_loans', 'interest_benefit_amount')) {
            Schema::table('employer_loans', function (Blueprint $table) {
                $table->decimal('interest_benefit_amount', 10, 2)->default(0)->after('calculate_interest_benefit');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('employer_loans', 'interest_benefit_amount')) {
            Schema::table('employer_loans', function (Blueprint $table) {
                $table->dropColumn('interest_benefit_amount');
            });
        }
    }
};
