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
        Schema::table('pay_slips', function (Blueprint $table) {
            $table->foreignId('tax_year_id')->nullable()->after('tax_bracket')
                  ->constrained('tax_years')->nullOnDelete();
            $table->decimal('paye_amount', 10, 2)->nullable()->after('tax_year_id');
            $table->decimal('uif_amount', 10, 2)->nullable()->after('paye_amount');
            $table->decimal('sdl_amount', 10, 2)->nullable()->after('uif_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pay_slips', function (Blueprint $table) {
            $table->dropForeign(['tax_year_id']);
            $table->dropColumn(['tax_year_id', 'paye_amount', 'uif_amount', 'sdl_amount']);
        });
    }
};
