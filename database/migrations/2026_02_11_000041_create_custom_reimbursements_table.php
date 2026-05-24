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
        Schema::create('custom_reimbursements', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('input_type')->nullable();
            $table->boolean('different_rate_for_every_employee')->default(false);
            $table->decimal('custom_rate', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('custom_reimbursements');
    }
};
