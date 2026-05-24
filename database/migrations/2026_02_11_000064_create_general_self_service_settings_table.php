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
        Schema::create('general_self_service_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('auto_enable')->default(false);
            $table->boolean('attach_payslips')->default(false);
            $table->boolean('enable_password_protection')->default(false);
            $table->boolean('allow_tax_certificates')->default(false);
            $table->boolean('attach_certificates')->default(false);
            $table->boolean('disable_leave_requests')->default(false);
            $table->boolean('disable_info_requests')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_self_service_settings');
    }
};
