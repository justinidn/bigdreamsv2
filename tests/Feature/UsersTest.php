<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UsersTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_login_checkbox_assigns_system_login_permission(): void
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);
        $newUserDepartment = Department::query()->create([
            'department_name' => 'New User Department',
            'department_alias' => 'NEW',
        ]);
        $admin = User::factory()->create(['department_id' => $department->id]);
        $admin->givePermissionTo(Permission::query()->create([
            'name' => 'users.create',
            'guard_name' => 'web',
        ]));
        $admin->givePermissionTo(Permission::query()->create([
            'name' => 'users.view',
            'guard_name' => 'web',
        ]));
        Permission::query()->create([
            'id' => 24,
            'name' => 'system.login',
            'guard_name' => 'web',
        ]);

        $response = $this->actingAs($admin)->postJson(route('users.store'), [
            'department_id' => (string) $newUserDepartment->id,
            'name' => 'Test User',
            'email' => 'test-user@example.test',
            'nrk' => '1234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'can_login' => true,
        ]);

        $response->assertCreated();

        $createdUser = User::query()->where('email', 'test-user@example.test')->firstOrFail();
        $permission = Permission::query()->where('name', 'system.login')->firstOrFail();

        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_type' => User::class,
            'model_id' => $createdUser->id,
        ]);

        $this->actingAs($admin)
            ->getJson(route('users.index'))
            ->assertOk()
            ->assertJsonFragment([
                'id' => $createdUser->id,
                'can_login' => true,
            ]);
    }

    public function test_can_login_switch_assigns_and_revokes_system_login_permission_on_update(): void
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);
        $admin = User::factory()->create(['department_id' => $department->id]);
        $admin->givePermissionTo(Permission::query()->create([
            'name' => 'users.update',
            'guard_name' => 'web',
        ]));
        $permission = Permission::query()->create([
            'id' => 24,
            'name' => 'system.login',
            'guard_name' => 'web',
        ]);
        $user = User::factory()->create(['department_id' => $department->id]);
        $userData = [
            'department_id' => $department->id,
            'name' => $user->name,
            'email' => $user->email,
            'nrk' => $user->nrk,
        ];

        $this->actingAs($admin)
            ->putJson(route('users.update', $user), [...$userData, 'can_login' => true])
            ->assertOk();

        $this->assertDatabaseHas('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);

        $this->actingAs($admin)
            ->putJson(route('users.update', $user), [...$userData, 'can_login' => false])
            ->assertOk();

        $this->assertDatabaseMissing('model_has_permissions', [
            'permission_id' => $permission->id,
            'model_type' => User::class,
            'model_id' => $user->id,
        ]);
    }

    public function test_user_status_filter_returns_only_users_with_requested_status(): void
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);
        $admin = User::factory()->create(['department_id' => $department->id]);
        $admin->givePermissionTo(Permission::query()->create([
            'name' => 'users.view',
            'guard_name' => 'web',
        ]));
        $inactiveUser = User::factory()->create([
            'department_id' => $department->id,
            'is_active' => false,
        ]);

        $this->actingAs($admin)
            ->getJson(route('users.index', ['status' => '0']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $inactiveUser->id)
            ->assertJsonPath('data.0.is_active', false)
            ->assertJsonCount(1, 'data');
    }
}
