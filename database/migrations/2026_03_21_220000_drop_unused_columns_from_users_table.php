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
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['next_billing_date']);
            $table->dropColumn([
                'billing_day',
                'next_billing_date',
                'billing_contact_name',
                'billing_contact_email',
                'billing_contact_phone',
                'referral_code',
                'used_referral_code',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->integer('billing_day')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->string('billing_contact_name')->nullable();
            $table->string('billing_contact_email')->nullable();
            $table->string('billing_contact_phone')->nullable();
            $table->string('referral_code')->nullable();
            $table->string('used_referral_code')->nullable();
            $table->index('next_billing_date');
        });
    }
};
