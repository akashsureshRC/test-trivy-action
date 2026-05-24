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
        Schema::create('master_admin_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('master_admin_id');
            $table->unsignedBigInteger('company_id');
            $table->timestamps();
            $table->unique(['master_admin_id', 'company_id']);
            $table->foreign('company_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('master_admin_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_admin_companies');
    }
};
