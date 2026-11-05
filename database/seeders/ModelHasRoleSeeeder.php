<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ModelHasRoleSeeeder extends Seeder
{
    public function run(): void
    {
        DB::table('model_has_roles')->upsert(
            [
                [
                    'role_id'    => 1,
                    'model_type' => 'App\Models\User',
                    'model_id'   => 1,
                ],
            ],
            ['role_id', 'model_id', 'model_type'],
            []
        );
    }
}
