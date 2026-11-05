<?php

namespace Tests\Feature;

use App\Models\Departments;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DepartmentsTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_department(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->postJson(route('departments.store'), [
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.department_name', 'Human Resources')
            ->assertJsonPath('data.department_alias', 'HR');

        $this->assertDatabaseHas('master_departments', [
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
            'is_active' => true,
            'created_by' => $user->id,
        ]);
    }

    public function test_authenticated_user_can_update_department_without_changing_its_alias(): void
    {
        $user = User::factory()->create();
        $department = Departments::query()->create([
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->patchJson(route('departments.update', $department), [
            'department_name' => 'People Operations',
            'is_active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.department_name', 'People Operations')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('master_departments', [
            'id' => $department->id,
            'department_name' => 'People Operations',
            'department_alias' => 'HR',
            'is_active' => false,
            'updated_by' => $user->id,
        ]);
    }

    public function test_department_update_rejects_an_alias_used_by_another_department(): void
    {
        $user = User::factory()->create();
        $department = Departments::query()->create([
            'department_name' => 'Information Technology',
            'department_alias' => 'IT',
        ]);
        Departments::query()->create([
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
        ]);

        $response = $this->actingAs($user)->patchJson(route('departments.update', $department), [
            'department_alias' => 'HR',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['department_alias']);

        $this->assertDatabaseHas('master_departments', [
            'id' => $department->id,
            'department_name' => 'Information Technology',
            'department_alias' => 'IT',
        ]);
    }

    public function test_department_can_be_updated_when_its_unchanged_alias_already_exists_in_legacy_data(): void
    {
        $user = User::factory()->create();
        $department = Departments::query()->create([
            'department_name' => 'Information Technology',
            'department_alias' => 'IT',
        ]);
        Departments::query()->create([
            'department_name' => 'Technical Support',
            'department_alias' => 'IT',
        ]);

        $response = $this->actingAs($user)->patchJson(route('departments.update', $department), [
            'department_name' => 'Technology',
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.department_name', 'Technology')
            ->assertJsonPath('data.department_alias', 'IT');

        $this->assertDatabaseHas('master_departments', [
            'id' => $department->id,
            'department_name' => 'Technology',
            'department_alias' => 'IT',
        ]);
    }

    public function test_department_in_use_by_a_user_cannot_be_deleted(): void
    {
        $department = Departments::query()->create([
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
        ]);
        $user = $this->userWhoCanDeleteDepartments();
        User::factory()->create(['department_id' => $department->id]);

        $response = $this->actingAs($user)->deleteJson(route('departments.destroy', $department));

        $response
            ->assertUnprocessable()
            ->assertJsonPath('status', 'error')
            ->assertJsonPath('message', 'Data tidak dapat dihapus karena masih digunakan.');

        $this->assertModelExists($department);
    }

    public function test_department_not_in_use_can_be_deleted(): void
    {
        $department = Departments::query()->create([
            'department_name' => 'Human Resources',
            'department_alias' => 'HR',
        ]);
        $user = $this->userWhoCanDeleteDepartments();

        $response = $this->actingAs($user)->deleteJson(route('departments.destroy', $department));

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('message', 'Data berhasil dihapus.');

        $this->assertDatabaseMissing('master_departments', ['id' => $department->id]);
    }

    private function userWhoCanDeleteDepartments(): User
    {
        $userDepartment = Departments::query()->create([
            'department_name' => 'Administration',
            'department_alias' => 'ADMIN',
        ]);
        $user = User::factory()->create(['department_id' => $userDepartment->id]);
        $user->givePermissionTo(Permission::findOrCreate('departments.delete', 'web'));

        return $user;
    }
}
