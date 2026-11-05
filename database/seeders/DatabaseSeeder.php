<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            DepartmentSeeder::class,
            MasterUserSeeder::class,
            MasterMenuSeeder::class,
            PermissionSeeder::class,
            RolesSeeder::class,
            RoleHasPermissionsSeeder::class,
            ModelHasPermissionSeeeder::class,
            ModelHasRoleSeeeder::class,

        ]);
    }
}
