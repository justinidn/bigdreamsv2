<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasPermissionSeeeder extends Seeder
{
    public function run(): void
    {
        $permissions = DB::table('permissions')->pluck('id');

        $data = [];
        foreach ($permissions as $permissionId) {
            $data[] = [
                'permission_id' => $permissionId,
                'model_type'    => 'App\Models\User',
                'model_id'      => 1,
            ];
        }

        DB::table('model_has_permissions')->upsert(
            $data,
            ['permission_id', 'model_id', 'model_type'],
            []
        );
    }
}
