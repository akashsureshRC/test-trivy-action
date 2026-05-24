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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('profile_pic_path')->nullable();
            $table->string('employee_id')->unique();
            $table->string('salutation')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->integer('department_id')->nullable();
            $table->integer('designation_id')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('password')->nullable();
            $table->string('ess_setup_token', 64)->nullable();
            $table->timestamp('ess_setup_token_expires_at')->nullable();
            $table->boolean('ess_enabled')->default(false);
            $table->timestamp('ess_last_login_at')->nullable();
            $table->rememberToken();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('temp_flat_no')->nullable();
            $table->string('temp_pincode')->nullable();
            $table->string('temp_street')->nullable();
            $table->string('temp_city')->nullable();
            $table->string('temp_state')->nullable();
            $table->string('temp_country')->nullable();
            $table->string('flat_no')->nullable();
            $table->string('pincode')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->string('status')->default('Active');
            $table->bigInteger('pay_frequency')->nullable();
            $table->string('bank')->nullable();
            $table->string('account_number')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('branch_code')->nullable();
            $table->string('account_type')->nullable();
            $table->string('holder_relationship')->nullable();
            $table->string('date_of_appointment')->nullable();
            $table->string('identification_type')->nullable();
            $table->string('id_number')->nullable();
            $table->string('passport_country')->nullable();
            $table->string('tax_reference_number')->nullable();
            $table->bigInteger('workspace_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->boolean('attendance_enabled')->default(false);
            $table->boolean('use_custom_working_hours')->default(false);
            $table->softDeletes();
            $table->string('deletion_reason')->nullable();
            $table->timestamps();
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
