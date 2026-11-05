<?php

namespace Tests\Feature;

use App\Models\Department;
use App\Models\MasterMenu;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MasterMenusPermissionCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_creates_permissions_for_enabled_menu_actions(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();
        Permission::findOrCreate('inventory.view', 'web');

        $response = $this->actingAs($user)->postJson(route('master-menus.store'), [
            'name' => 'inventory',
            'display_name' => 'Inventory Management',
            'can_view' => true,
            'can_create' => false,
            'can_update' => true,
            'can_delete' => false,
            'can_export' => false,
            'custom_permissions' => [
                ['action' => 'Approve', 'enabled' => true],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.name', 'inventory');

        $this->assertDatabaseHas('master_menus', [
            'name' => 'inventory',
            'display_name' => 'Inventory Management',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.view',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.update',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.create']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.delete']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.export']);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.approve',
            'guard_name' => 'web',
        ]);
        $response->assertJsonPath('data.custom_permissions.0.action', 'approve');
        $this->assertSame(1, Permission::query()->where('name', 'inventory.view')->count());
    }

    public function test_store_rejects_invalid_permission_switch_values_without_creating_records(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();

        $response = $this->actingAs($user)->postJson(route('master-menus.store'), [
            'name' => 'inventory',
            'display_name' => 'Inventory',
            'can_view' => 'enabled',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonPath('errors.can_view.0', 'Hak akses view harus bernilai benar atau salah.');

        $this->assertDatabaseMissing('master_menus', ['name' => 'inventory']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.view']);
    }

    public function test_new_module_name_is_normalized_and_appears_with_switches(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();

        $this->actingAs($user)
            ->postJson(route('master-menus.store'), [
                'name' => 'Inventory Tools',
                'display_name' => 'Stock Management',
            ])
            ->assertCreated()
            ->assertJsonPath('data.name', 'inventory-tools')
            ->assertJsonPath('data.display_name', 'Stock Management')
            ->assertJsonPath('data.can_view', false)
            ->assertJsonPath('data.can_export', false);

        $this->assertDatabaseHas('master_menus', [
            'name' => 'inventory-tools',
            'display_name' => 'Stock Management',
        ]);
    }

    public function test_master_menu_page_lists_modules_with_manual_permission_switches(): void
    {
        $user = $this->userWhoCanViewMasterMenus();
        $user->givePermissionTo(Permission::findOrCreate('master-menus.update', 'web'));
        $masterMenu = MasterMenu::query()->create([
            'name' => 'inventory',
            'display_name' => 'Inventory',
        ]);
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.export', 'web');

        $this->actingAs($user)
            ->get(route('master-menus.index'))
            ->assertOk()
            ->assertSee('Create')
            ->assertSee('Update')
            ->assertSee('Delete')
            ->assertSee('View')
            ->assertSee('Export')
            ->assertSee('editItem(item)')
            ->assertSee('edit-master-menu-modal')
            ->assertDontSee('@change="updatePermission')
            ->assertSee('open-master-menu-modal')
            ->assertSee('id="masterMenuDisplayName"', false)
            ->assertSee('id="customPermissionName"', false)
            ->assertSee('Permission custom')
            ->assertSee('x-if="showCustomPermissionInput"', false)
            ->assertSee('@click="showCustomPermissionInput = true"', false)
            ->assertSee('prepareCustomPermissionForSave()')
            ->assertSee('x-model="form.can_view"', false)
            ->assertSee('x-model="form.can_create"', false)
            ->assertSee('x-model="form.can_update"', false)
            ->assertSee('x-model="form.can_delete"', false)
            ->assertSee('x-model="form.can_export"', false);

        $this->actingAs($user)
            ->getJson(route('master-menus.index', ['search' => 'inventory']))
            ->assertOk()
            ->assertJsonPath('data.0.id', $masterMenu->id)
            ->assertJsonPath('data.0.can_view', true)
            ->assertJsonPath('data.0.can_create', false)
            ->assertJsonPath('data.0.can_export', true);
    }

    public function test_edit_returns_existing_permission_states_for_switches(): void
    {
        $user = $this->userWhoCanUpdateMasterMenus();
        $masterMenu = MasterMenu::query()->create([
            'name' => 'inventory',
            'display_name' => 'Inventory',
        ]);
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.update', 'web');
        Permission::findOrCreate('inventory.export', 'web');
        Permission::findOrCreate('inventory.approve', 'web');

        $response = $this->actingAs($user)->getJson(route('master-menus.edit', $masterMenu));

        $response
            ->assertOk()
            ->assertJsonPath('data.can_view', true)
            ->assertJsonPath('data.can_create', false)
            ->assertJsonPath('data.can_update', true)
            ->assertJsonPath('data.can_delete', false)
            ->assertJsonPath('data.can_export', true)
            ->assertJsonPath('data.custom_permissions.0.action', 'approve')
            ->assertJsonPath('data.custom_permissions.0.enabled', true);
    }

    public function test_update_syncs_changed_switches_and_preserves_unchanged_permissions(): void
    {
        $user = $this->userWhoCanUpdateMasterMenus();
        $masterMenu = MasterMenu::query()->create([
            'name' => 'inventory',
            'display_name' => 'Inventory',
        ]);
        Permission::findOrCreate('inventory.view', 'web');
        Permission::findOrCreate('inventory.update', 'web');
        Permission::findOrCreate('inventory.approve', 'web');

        $response = $this->actingAs($user)->putJson(route('master-menus.update', $masterMenu), [
            'display_name' => 'Inventory Management',
            'can_view' => false,
            'can_export' => true,
            'custom_permissions' => [
                ['action' => 'approve', 'enabled' => false],
                ['action' => 'review', 'enabled' => true],
            ],
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('status', 'success');

        $this->assertDatabaseMissing('permissions', [
            'name' => 'inventory.view',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.update',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.export',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseMissing('permissions', [
            'name' => 'inventory.approve',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('permissions', [
            'name' => 'inventory.review',
            'guard_name' => 'web',
        ]);
        $this->assertDatabaseHas('master_menus', [
            'id' => $masterMenu->id,
            'display_name' => 'Inventory Management',
        ]);
    }

    public function test_user_without_create_permission_cannot_create_menu_permissions(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson(route('master-menus.store'), [
            'name' => 'inventory',
            'display_name' => 'Inventory',
            'can_view' => true,
        ]);

        $response->assertForbidden();

        $this->assertDatabaseMissing('master_menus', ['name' => 'inventory']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.view']);
    }

    public function test_user_without_update_permission_cannot_change_menu_switches(): void
    {
        $user = $this->createUser();
        $masterMenu = MasterMenu::query()->create([
            'name' => 'inventory',
            'display_name' => 'Inventory',
        ]);

        $this->actingAs($user)
            ->putJson(route('master-menus.update', $masterMenu), [
                'can_view' => true,
            ])
            ->assertForbidden();

        $this->assertDatabaseMissing('permissions', [
            'name' => 'inventory.view',
            'guard_name' => 'web',
        ]);
    }

    public function test_store_rejects_reserved_custom_permission_names(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();

        $this->actingAs($user)
            ->postJson(route('master-menus.store'), [
                'name' => 'inventory',
                'display_name' => 'Inventory',
                'custom_permissions' => [
                    ['action' => 'view', 'enabled' => true],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('custom_permissions.0.action');

        $this->assertDatabaseMissing('master_menus', ['name' => 'inventory']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.view']);
    }

    public function test_store_rejects_custom_permission_names_outside_the_action_format(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();

        $this->actingAs($user)
            ->postJson(route('master-menus.store'), [
                'name' => 'inventory',
                'display_name' => 'Inventory',
                'custom_permissions' => [
                    ['action' => 'approve.now', 'enabled' => true],
                ],
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('custom_permissions.0.action');

        $this->assertDatabaseMissing('master_menus', ['name' => 'inventory']);
        $this->assertDatabaseMissing('permissions', ['name' => 'inventory.approve.now']);
    }

    public function test_store_requires_a_display_name(): void
    {
        $user = $this->userWhoCanCreateMasterMenus();

        $this->actingAs($user)
            ->postJson(route('master-menus.store'), ['name' => 'inventory'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('display_name');

        $this->assertDatabaseMissing('master_menus', ['name' => 'inventory']);
    }

    private function userWhoCanCreateMasterMenus(): User
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('master-menus.create', 'web'));

        return $user;
    }

    private function userWhoCanViewMasterMenus(): User
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('master-menus.view', 'web'));

        return $user;
    }

    private function userWhoCanUpdateMasterMenus(): User
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('master-menus.update', 'web'));

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
