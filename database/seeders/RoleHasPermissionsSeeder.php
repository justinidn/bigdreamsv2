<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleHasPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = DB::table('permissions')->pluck('id');

        $data = [];
        foreach ($permissions as $permissionId) {
            $data[] = [
                'role_id'       => 1,
                'permission_id' => $permissionId,
            ];
        }

        DB::table('role_has_permissions')->upsert(
            $data,
            ['role_id', 'permission_id'],
            []
        );
    }
}
