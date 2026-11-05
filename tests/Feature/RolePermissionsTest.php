<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class RolePermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_form_groups_permissions_under_their_module(): void
    {
        $admin = $this->userWithPermission('roles.view');
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.create', 'web');
        Permission::findOrCreate('system.login', 'web');

        $this->actingAs($admin)
            ->get(route('roles.index'))
            ->assertOk()
            ->assertSee('Inventory')
            ->assertSee('inventory.view')
            ->assertSee('inventory.create')
            ->assertDontSee('system.login');
    }

    public function test_store_assigns_only_selected_permissions(): void
    {
        $admin = $this->userWithPermission('roles.create');
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.update', 'web');
        Permission::findOrCreate('reports.view', 'web');

        $response = $this->actingAs($admin)->postJson(route('roles.store'), [
            'name' => 'Inventory Manager',
            'permissions' => ['inventory.view'],
        ]);

        $response->assertCreated();

        $role = Role::query()->where('name', 'Inventory Manager')->firstOrFail();
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => Permission::findByName('inventory.view', 'web')->id,
        ]);
        $this->assertDatabaseMissing('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => Permission::findByName('inventory.update', 'web')->id,
        ]);
    }

    public function test_update_syncs_individual_permissions(): void
    {
        $admin = $this->userWithPermission('roles.update');
        $inventoryView = Permission::findOrCreate('inventory.view', 'web');
        $inventoryUpdate = Permission::findOrCreate('inventory.update', 'web');
        $reportsView = Permission::findOrCreate('reports.view', 'web');
        $role = Role::query()->create([
            'name' => 'Editor',
            'guard_name' => 'web',
        ]);
        DB::table('role_has_permissions')->insert([
            ['role_id' => $role->id, 'permission_id' => $inventoryView->id],
            ['role_id' => $role->id, 'permission_id' => $inventoryUpdate->id],
            ['role_id' => $role->id, 'permission_id' => $reportsView->id],
        ]);

        $response = $this->actingAs($admin)->putJson(route('roles.update', $role), [
            'permissions' => ['reports.view', 'inventory.update'],
        ]);

        $response->assertOk();
        $this->assertDatabaseMissing('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $inventoryView->id,
        ]);
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $inventoryUpdate->id,
        ]);
        $this->assertDatabaseHas('role_has_permissions', [
            'role_id' => $role->id,
            'permission_id' => $reportsView->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('roles.edit', $role))
            ->assertOk()
            ->assertJsonPath('data.enabled_permissions', ['inventory.update', 'reports.view']);
    }

    public function test_store_rejects_unknown_permissions(): void
    {
        $admin = $this->userWithPermission('roles.create');

        $response = $this->actingAs($admin)->postJson(route('roles.store'), [
            'name' => 'Invalid Role',
            'permissions' => ['not-a-permission'],
        ]);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors('permissions.0');

        $this->assertDatabaseMissing('roles', ['name' => 'Invalid Role']);
    }

    private function userWithPermission(string $permissionName): User
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);
        $user = User::factory()->create(['department_id' => $department->id]);
        $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));

        return $user;
    }
}
