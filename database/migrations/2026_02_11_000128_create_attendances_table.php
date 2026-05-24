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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id');
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->date('date');
            $table->string('status');
            $table->time('clock_in')->nullable();
            $table->decimal('clock_in_latitude', 10, 8)->nullable();
            $table->decimal('clock_in_longitude', 11, 8)->nullable();
            $table->time('clock_out')->nullable();
            $table->boolean('requires_hr_review')->default(false);
            $table->timestamp('hr_reviewed_at')->nullable();
            $table->unsignedBigInteger('hr_reviewed_by')->nullable();
            $table->text('hr_notes')->nullable();
            $table->decimal('clock_out_latitude', 10, 8)->nullable();
            $table->decimal('clock_out_longitude', 11, 8)->nullable();
            $table->tinyInteger('marked_by')->default(1);
            $table->integer('workspace')->nullable();
            $table->integer('created_by');
            $table->timestamps();
            $table->index(['employee_id', 'date']);
            $table->index(['requires_hr_review', 'hr_reviewed_at']);
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
            $table->foreign('hr_reviewed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
