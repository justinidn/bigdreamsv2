<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('master_menus', function (Blueprint $table) {
            $table->unique('name', 'master_menus_name_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('master_menus', function (Blueprint $table) {
            $table->dropUnique('master_menus_name_unique');
        });
    }
};
