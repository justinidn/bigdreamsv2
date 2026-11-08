<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterMenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['id' => 1, 'name' => 'users',            'display_name' => 'Users'],
            ['id' => 2, 'name' => 'departments',      'display_name' => 'Departments'],
            ['id' => 3, 'name' => 'master-menus',     'display_name' => 'Master Menus'],
            ['id' => 4, 'name' => 'roles',            'display_name' => 'Roles'],
            ['id' => 5, 'name' => 'user-permissions', 'display_name' => 'User Permissions'],
            ['id' => 6, 'name' => 'user-roles',       'display_name' => 'User Roles'],
            ['id' => 7, 'name' => 'fixed-asset-depreciations',       'display_name' => 'Fixed Asset Depreciations'],
        ];

        foreach ($menus as $menu) {
            DB::table('master_menus')->updateOrInsert(
                ['id' => $menu['id']],
                [
                    'name' => $menu['name'],
                    'display_name' => $menu['display_name'],
                    'created_by' => 1,
                    'updated_by' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
