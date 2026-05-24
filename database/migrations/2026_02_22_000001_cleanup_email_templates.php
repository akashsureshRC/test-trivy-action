<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * 1. Drop module_name column from email_templates and email_template_langs
     * 2. Delete 8 unused email templates and their translations:
     *    New Award, Employee Transfer, Employee Resignation, Employee Trip,
     *    Employee Promotion, Employee Complaints, Employee Warning, Employee Termination
     * 3. Delete unused "Leave Status" template (superseded by per-status templates)
     */
    public function up(): void
    {
        // 1. Remove unused email templates and their translations
        $unusedTemplates = [
            'New Award',
            'Employee Transfer',
            'Employee Resignation',
            'Employee Trip',
            'Employee Promotion',
            'Employee Complaints',
            'Employee Warning',
            'Employee Termination',
            'Leave Status',
        ];

        $templateIds = DB::table('email_templates')
            ->whereIn('name', $unusedTemplates)
            ->pluck('id');

        if ($templateIds->isNotEmpty()) {
            DB::table('email_template_langs')
                ->whereIn('parent_id', $templateIds)
                ->delete();

            DB::table('email_templates')
                ->whereIn('id', $templateIds)
                ->delete();
        }

        // 2. Drop module_name column from email_templates
        if (Schema::hasColumn('email_templates', 'module_name')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->dropColumn('module_name');
            });
        }

        // 3. Drop module_name column from email_template_langs
        if (Schema::hasColumn('email_template_langs', 'module_name')) {
            Schema::table('email_template_langs', function (Blueprint $table) {
                $table->dropColumn('module_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add module_name columns
        if (!Schema::hasColumn('email_templates', 'module_name')) {
            Schema::table('email_templates', function (Blueprint $table) {
                $table->string('module_name')->nullable()->after('from');
            });
        }

        if (!Schema::hasColumn('email_template_langs', 'module_name')) {
            Schema::table('email_template_langs', function (Blueprint $table) {
                $table->string('module_name')->nullable()->after('content');
            });
        }

        // Note: Deleted template data cannot be automatically restored.
        // Run the seeder to recreate them if needed.
    }
};
