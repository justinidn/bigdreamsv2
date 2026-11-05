<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserPermissionAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_permission_page_groups_permissions_and_excludes_system_login_and_create_delete_controls(): void
    {
        $admin = $this->userWithPermission('user-permissions.view');
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.update', 'web');
        Permission::findOrCreate('system.login', 'web');

        $this->actingAs($admin)
            ->get(route('user-permissions.index'))
            ->assertOk()
            ->assertSee('Inventory')
            ->assertSee('inventory.view')
            ->assertSee('inventory.update')
            ->assertDontSee('system.login')
            ->assertDontSee('Tambah')
            ->assertDontSee('deleteItem(');
    }

    public function test_user_permission_list_contains_direct_permissions_but_not_role_permissions(): void
    {
        $admin = $this->userWithPermission('user-permissions.view');
        $user = $this->createUser('Target User');
        $directPermission = Permission::findOrCreate('inventory.view', 'web');
        $rolePermission = Permission::findOrCreate('reports.view', 'web');
        $role = Role::findOrCreate('report-reader', 'web');
        $role->givePermissionTo($rolePermission);
        $user->assignRole($role);
        $user->givePermissionTo($directPermission);

        $this->actingAs($admin)
            ->getJson(route('user-permissions.index', ['search' => 'Target User']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $user->id)
            ->assertJsonPath('data.0.permission_names', ['inventory.view']);
    }

    public function test_update_syncs_direct_permissions_and_preserves_system_login(): void
    {
        $admin = $this->userWithPermission('user-permissions.update');
        $user = $this->createUser();
        $oldPermission = Permission::findOrCreate('inventory.view', 'web');
        $newPermission = Permission::findOrCreate('reports.view', 'web');
        $loginPermission = Permission::findOrCreate('system.login', 'web');
        $user->givePermissionTo($oldPermission, $loginPermission);

        $this->actingAs($admin)
            ->putJson(route('user-permissions.update', $user), [
                'permissions' => ['reports.view'],
            ])
            ->assertOk()
            ->assertJsonPath('data.permission_names', ['reports.view']);

        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $oldPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $newPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $loginPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_update_removes_managed_permissions_when_all_switches_are_off(): void
    {
        $admin = $this->userWithPermission('user-permissions.update');
        $user = $this->createUser();
        $directPermission = Permission::findOrCreate('inventory.view', 'web');
        $loginPermission = Permission::findOrCreate('system.login', 'web');
        $user->givePermissionTo($directPermission, $loginPermission);

        $this->actingAs($admin)
            ->putJson(route('user-permissions.update', $user), ['permissions' => []])
            ->assertOk()
            ->assertJsonPath('data.permission_names', []);

        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $directPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $loginPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_update_rejects_unknown_permissions_without_changing_assignments(): void
    {
        $admin = $this->userWithPermission('user-permissions.update');
        $user = $this->createUser();
        $existingPermission = Permission::findOrCreate('inventory.view', 'web');
        $user->givePermissionTo($existingPermission);

        $this->actingAs($admin)
            ->putJson(route('user-permissions.update', $user), [
                'permissions' => ['not-a-permission'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('permissions.0');

        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $existingPermission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_update_rejects_system_login_permission(): void
    {
        $admin = $this->userWithPermission('user-permissions.update');
        $user = $this->createUser();
        Permission::findOrCreate('system.login', 'web');

        $this->actingAs($admin)
            ->putJson(route('user-permissions.update', $user), [
                'permissions' => ['system.login'],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('permissions.0');
    }

    public function test_user_without_update_permission_cannot_change_user_permissions(): void
    {
        $user = $this->createUser();

        $this->actingAs($this->createUser())
            ->putJson(route('user-permissions.update', $user), ['permissions' => []])
            ->assertForbidden();
    }

    public function test_user_permissions_cannot_be_created_or_deleted_from_this_page(): void
    {
        $admin = $this->userWithPermission('user-permissions.update');
        $user = $this->createUser();

        $this->actingAs($admin)
            ->postJson(route('user-permissions.index'), [])
            ->assertMethodNotAllowed();

        $this->actingAs($admin)
            ->deleteJson(route('user-permissions.update', $user))
            ->assertMethodNotAllowed();
    }

    private function userWithPermission(string $permissionName): User
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));

        return $user;
    }

    private function createUser(string $name = 'Test User'): User
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);

        return User::factory()->create([
            'name' => $name,
            'department_id' => $department->id,
        ]);
    }
}
