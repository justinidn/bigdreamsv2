<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            ['id' => 1,  'name' => 'users.view',            'guard_name' => 'web'],
            ['id' => 2,  'name' => 'users.create',          'guard_name' => 'web'],
            ['id' => 3,  'name' => 'users.update',          'guard_name' => 'web'],
            ['id' => 4,  'name' => 'users.delete',          'guard_name' => 'web'],

            // Departments (Sinkron dengan Route)
            ['id' => 5,  'name' => 'departments.view',      'guard_name' => 'web'],
            ['id' => 6,  'name' => 'departments.create',    'guard_name' => 'web'],
            ['id' => 7,  'name' => 'departments.update',    'guard_name' => 'web'],
            ['id' => 8,  'name' => 'departments.delete',    'guard_name' => 'web'],

            // Master Menus
            ['id' => 9,  'name' => 'master-menus.view',     'guard_name' => 'web'],
            ['id' => 10, 'name' => 'master-menus.create',   'guard_name' => 'web'],
            ['id' => 11, 'name' => 'master-menus.update',   'guard_name' => 'web'],
            ['id' => 12, 'name' => 'master-menus.delete',   'guard_name' => 'web'],

            // Roles
            ['id' => 13, 'name' => 'roles.view',            'guard_name' => 'web'],
            ['id' => 14, 'name' => 'roles.create',          'guard_name' => 'web'],
            ['id' => 15, 'name' => 'roles.update',          'guard_name' => 'web'],
            ['id' => 16, 'name' => 'roles.delete',          'guard_name' => 'web'],

            // User Permissions
            ['id' => 17, 'name' => 'user-permissions.view', 'guard_name' => 'web'],
            ['id' => 18, 'name' => 'user-permissions.create', 'guard_name' => 'web'],
            ['id' => 19, 'name' => 'user-permissions.update', 'guard_name' => 'web'],
            ['id' => 20, 'name' => 'user-permissions.delete', 'guard_name' => 'web'],

            // User Roles
            ['id' => 21, 'name' => 'user-roles.view',       'guard_name' => 'web'],
            ['id' => 22, 'name' => 'user-roles.create',     'guard_name' => 'web'],
            ['id' => 23, 'name' => 'user-roles.update',     'guard_name' => 'web'],

            // System
            ['id' => 24, 'name' => 'system.login',          'guard_name' => 'web'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['id' => $permission['id']],
                [
                    'name' => $permission['name'],
                    'guard_name' => $permission['guard_name'],
                ]
            );
        }
    }
}
