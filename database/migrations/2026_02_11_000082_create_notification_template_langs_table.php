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
        Schema::create('notification_template_langs', function (Blueprint $table) {
            $table->id();
            $table->integer('parent_id')->default(0);
            $table->string('lang')->nullable();
            $table->string('module')->nullable();
            $table->text('content')->nullable();
            $table->longText('variables')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_template_langs');
    }
};
