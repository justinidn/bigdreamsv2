<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserRoleAssignmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_page_shows_existing_role_options_without_create_or_delete_controls(): void
    {
        $admin = $this->userWithPermission('user-roles.view');
        Role::findOrCreate('editor', 'web');

        $this->actingAs($admin)
            ->get(route('user-roles.index'))
            ->assertOk()
            ->assertSee('Atur Role User')
            ->assertSee('editor')
            ->assertDontSee('Tambah UserRole Baru')
            ->assertDontSee('deleteItem(');
    }

    public function test_user_role_page_lists_existing_users_and_their_roles(): void
    {
        $admin = $this->userWithPermission('user-roles.view');
        $user = $this->createUser();
        $role = Role::findOrCreate('editor', 'web');
        $user->assignRole($role);

        $this->actingAs($admin)
            ->getJson(route('user-roles.index'))
            ->assertOk()
            ->assertJsonPath('data.0.id', $user->id)
            ->assertJsonPath('data.0.roles.0.name', 'editor')
            ->assertJsonPath('data.0.role_ids.0', $role->id);
    }

    public function test_update_syncs_multiple_roles_for_an_existing_user(): void
    {
        $admin = $this->userWithPermission('user-roles.update');
        $user = $this->createUser();
        $previousRole = Role::findOrCreate('previous', 'web');
        $editorRole = Role::findOrCreate('editor', 'web');
        $managerRole = Role::findOrCreate('manager', 'web');
        $user->assignRole($previousRole);

        $response = $this->actingAs($admin)
            ->putJson(route('user-roles.update', $user), [
                'roles' => [$editorRole->id, $managerRole->id],
            ]);

        $response->assertOk()
            ->assertJsonPath('data.role_ids', [$editorRole->id, $managerRole->id]);

        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $previousRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $editorRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
        $this->assertDatabaseHas('model_has_roles', [
            'role_id' => $managerRole->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_update_can_remove_all_roles_from_an_existing_user(): void
    {
        $admin = $this->userWithPermission('user-roles.update');
        $user = $this->createUser();
        $role = Role::findOrCreate('editor', 'web');
        $user->assignRole($role);

        $this->actingAs($admin)
            ->putJson(route('user-roles.update', $user), ['roles' => []])
            ->assertOk();

        $this->assertDatabaseMissing('model_has_roles', [
            'role_id' => $role->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_user_role_assignment_rejects_nonexistent_roles(): void
    {
        $admin = $this->userWithPermission('user-roles.update');
        $user = $this->createUser();

        $this->actingAs($admin)
            ->putJson(route('user-roles.update', $user), ['roles' => [999999]])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('roles.0');
    }

    public function test_user_roles_cannot_be_created_or_deleted_from_this_page(): void
    {
        $admin = $this->userWithPermission('user-roles.update');
        $user = $this->createUser();

        $this->actingAs($admin)
            ->postJson(route('user-roles.index'), [])
            ->assertMethodNotAllowed();

        $this->actingAs($admin)
            ->deleteJson(route('user-roles.update', $user))
            ->assertMethodNotAllowed();
    }

    private function userWithPermission(string $permissionName): User
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate($permissionName, 'web'));

        return $user;
    }

    private function createUser(): User
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);

        return User::factory()->create(['department_id' => $department->id]);
    }
}
