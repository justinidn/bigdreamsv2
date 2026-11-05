<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'administrator', 'guard_name' => 'web'],
            ['id' => 2, 'name' => 'local',         'guard_name' => 'web'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['id' => $role['id']],
                [
                    'name'       => $role['name'],
                    'guard_name' => $role['guard_name'],
                ]
            );
        }
    }
}
