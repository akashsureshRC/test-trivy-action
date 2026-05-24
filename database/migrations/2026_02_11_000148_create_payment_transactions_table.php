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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('billing_payment_id')->nullable();
            $table->unsignedBigInteger('invoice_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('transaction_type');
            $table->string('gateway')->default('payfast');
            $table->string('gateway_transaction_id')->nullable();
            $table->string('gateway_reference')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('payment_status')->nullable();
            $table->json('request_data')->nullable();
            $table->json('response_data')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('signature')->nullable();
            $table->boolean('signature_valid')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index('gateway_transaction_id');
            $table->index('gateway_reference');
            $table->index(['user_id', 'transaction_type']);
            $table->foreign('billing_payment_id')->references('id')->on('billing_payments')->onDelete('set null');
            $table->foreign('invoice_id')->references('id')->on('invoices')->onDelete('set null');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
