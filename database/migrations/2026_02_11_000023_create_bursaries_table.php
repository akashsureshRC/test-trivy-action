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
        Schema::create('bursaries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('employee_id');
            $table->string('term');
            $table->decimal('taxable_portion', 10, 2)->default(0.00);
            $table->decimal('exempt_portion', 10, 2)->default(0.00);
            $table->string('Type');
            $table->boolean('employee_handles_payment')->default(false);
            $table->boolean('to_disabled_person')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bursaries');
    }
};
