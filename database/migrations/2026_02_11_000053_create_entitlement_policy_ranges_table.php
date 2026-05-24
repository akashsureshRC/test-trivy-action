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
        Schema::create('entitlement_policy_ranges', function (Blueprint $table) {
            $table->id();
            $table->string('entitlement_policy_id');
            $table->string('start_date')->nullable();
            $table->string('end_date')->nullable();
            $table->string('status')->default('Active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('entitlement_policy_ranges');
    }
};
