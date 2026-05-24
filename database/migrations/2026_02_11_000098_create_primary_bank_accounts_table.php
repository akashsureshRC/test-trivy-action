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
        Schema::create('primary_bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('eft_format');
            $table->string('bank');
            $table->string('account_number', 16)->unique();
            $table->string('branch_code', 6);
            $table->string('account_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('primary_bank_accounts');
    }
};
