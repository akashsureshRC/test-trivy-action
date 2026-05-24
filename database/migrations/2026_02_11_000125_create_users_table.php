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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('mobile_no')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('trial_ends_at')->nullable();
            $table->integer('trial_payslips_limit')->nullable();
            $table->integer('trial_payslips_used')->default(0);
            $table->unsignedInteger('payslips_count')->default(0);
            $table->string('billing_status')->default('trial');
            $table->timestamp('suspended_at')->nullable();
            $table->string('suspension_reason')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->string('type')->default('company');
            $table->integer('active_status')->default(1);
            $table->integer('active_workspace')->default(0);
            $table->string('avatar')->nullable();
            $table->boolean('dark_mode')->default(false);
            $table->string('lang', 191)->default('en');
            $table->string('messenger_color')->default('#2180f3');
            $table->integer('is_enable_login')->default(1);
            $table->integer('is_disable')->default(1);
            $table->integer('workspace_id')->default(0);
            $table->integer('created_by')->default(0);
            $table->timestamps();
            $table->index('trial_ends_at');
            $table->index('suspended_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
