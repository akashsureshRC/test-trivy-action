<?php

namespace Database\Seeders\Hrm;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
class DepartmentsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $departments = ['HR', 'Finance', 'IT', 'Sales'];
        $now = now();

        foreach ($departments as $departmentName) {
            $payload = ['name' => $departmentName];

            if (Schema::hasColumn('departments', 'status')) {
                $payload['status'] = 'Active';
            }
            if (Schema::hasColumn('departments', 'branch_id')) {
                $payload['branch_id'] = 0;
            }
            if (Schema::hasColumn('departments', 'workspace')) {
                $payload['workspace'] = 0;
            }
            if (Schema::hasColumn('departments', 'created_by')) {
                $payload['created_by'] = 0;
            }
            if (Schema::hasColumn('departments', 'created_at')) {
                $payload['created_at'] = $now;
            }
            if (Schema::hasColumn('departments', 'updated_at')) {
                $payload['updated_at'] = $now;
            }

            DB::table('departments')->updateOrInsert(
                ['name' => $departmentName],
                $payload
            );
        }
    }
}
