<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DepartmentSeeder extends Seeder
{
    public function run(): void
    {
        $departments = [
            ['id' => 1,  'department_alias' => 'MIS',   'department_name' => 'Management Information System', 'is_active' => true],
            ['id' => 2,  'department_alias' => 'HCCM',  'department_name' => 'Human Capital & Corporate Management', 'is_active' => true],
            ['id' => 4,  'department_alias' => 'FIN',   'department_name' => 'Finishing', 'is_active' => true],
            ['id' => 5,  'department_alias' => 'CONV',  'department_name' => 'Converting', 'is_active' => true],
            ['id' => 6,  'department_alias' => 'ACC',   'department_name' => 'Accounting', 'is_active' => true],
            ['id' => 7,  'department_alias' => 'ENG',   'department_name' => 'Engineering', 'is_active' => true],
            ['id' => 8,  'department_alias' => 'DEL',   'department_name' => 'Delivery', 'is_active' => true],
            ['id' => 9,  'department_alias' => 'WH',    'department_name' => 'Warehouse', 'is_active' => true],
            ['id' => 10, 'department_alias' => 'EXIM',  'department_name' => 'Exim', 'is_active' => true],
            ['id' => 11, 'department_alias' => 'P2K3',  'department_name' => 'P2K3L', 'is_active' => true],
            ['id' => 12, 'department_alias' => 'PPIC',  'department_name' => 'PPIC', 'is_active' => true],
            ['id' => 13, 'department_alias' => 'PURCH', 'department_name' => 'Purchasing', 'is_active' => true],
            ['id' => 14, 'department_alias' => 'QA',    'department_name' => 'Quality Assurance', 'is_active' => true],
            ['id' => 16, 'department_alias' => 'EKST',  'department_name' => 'Eksternal', 'is_active' => true],
        ];

        foreach ($departments as $department) {
            DB::table('departments')->updateOrInsert(
                ['id' => $department['id']],
                [
                    'department_alias' => $department['department_alias'],
                    'department_name' => $department['department_name'],
                    'is_active' => $department['is_active'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
