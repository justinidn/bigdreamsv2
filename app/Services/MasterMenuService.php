<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\MasterMenu;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MasterMenuService
{
    /**
     * Get paginated modules with their permission switch states.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedList(array $filters): LengthAwarePaginator
    {
        $query = MasterMenu::query();

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function ($query) use ($search): void {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('display_name', 'like', "%{$search}%");
            });
        }

        $menus = $query->orderBy('display_name')->orderBy('id')->paginate(10);
        $permissionNames = Permission::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $menus->getCollection()->transform(
            fn(MasterMenu $menu): MasterMenu => $this->addPermissionStates($menu, $permissionNames)
        );

        return $menus;
    }

    /**
     * Create a module and any initially enabled permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function store(array $data): MasterMenu
    {
        return DB::transaction(function () use ($data): MasterMenu {
            $menu = MasterMenu::query()->create([
                'name' => $data['name'],
                'display_name' => $data['display_name'],
            ]);

            $this->syncPermissionActions($menu, $data);
            $this->syncCustomPermissions($menu, $data);

            return $this->getMenuWithPermissionStates($menu->refresh());
        });
    }

    /**
     * Update the selected permission switches for a module.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(MasterMenu $masterMenu, array $data): MasterMenu
    {
        return DB::transaction(function () use ($masterMenu, $data): MasterMenu {
            if (array_key_exists('display_name', $data)) {
                $masterMenu->update(['display_name' => $data['display_name']]);
            }

            $this->syncPermissionActions($masterMenu, $data);
            $this->syncCustomPermissions($masterMenu, $data);

            return $this->getMenuWithPermissionStates($masterMenu->refresh());
        });
    }

    /**
     * Return a module with its current permission switch states.
     *
     * @return array<string, mixed>
     */
    public function getMenuData(MasterMenu $masterMenu): array
    {
        return $this->getMenuWithPermissionStates($masterMenu)->toArray();
    }

    /**
     * Create or remove only the permission switches provided in the request.
     *
     * @param  array<string, mixed>  $switches
     */
    private function syncPermissionActions(MasterMenu $menu, array $switches): void
    {
        $permissionsChanged = false;

        foreach (MasterMenu::PERMISSION_ACTIONS as $action) {
            $switch = "can_{$action}";

            if (! array_key_exists($switch, $switches)) {
                continue;
            }

            $permissionName = "{$menu->name}.{$action}";

            if ((bool) $switches[$switch]) {
                Permission::findOrCreate($permissionName, 'web');
                $permissionsChanged = true;

                continue;
            }

            $permission = Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($permission !== null) {
                $permission->delete();
                $permissionsChanged = true;
            }
        }

        if ($permissionsChanged) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Create or remove custom permissions submitted by the module form.
     *
     * @param  array<string, mixed>  $data
     */
    private function syncCustomPermissions(MasterMenu $menu, array $data): void
    {
        if (! array_key_exists('custom_permissions', $data)) {
            return;
        }

        $permissionsChanged = false;

        foreach ($data['custom_permissions'] as $customPermission) {
            $permissionName = "{$menu->name}.{$customPermission['action']}";

            if ((bool) $customPermission['enabled']) {
                Permission::findOrCreate($permissionName, 'web');
                $permissionsChanged = true;

                continue;
            }

            $permission = Permission::query()
                ->where('name', $permissionName)
                ->where('guard_name', 'web')
                ->first();

            if ($permission !== null) {
                $permission->delete();
                $permissionsChanged = true;
            }
        }

        if ($permissionsChanged) {
            app(PermissionRegistrar::class)->forgetCachedPermissions();
        }
    }

    /**
     * Add boolean permission states to a module.
     *
     * @param  array<int, string>|null  $permissionNames
     */
    private function getMenuWithPermissionStates(
        MasterMenu $menu,
        ?array $permissionNames = null
    ): MasterMenu {
        if ($permissionNames === null) {
            $permissionNames = Permission::query()
                ->where('guard_name', 'web')
                ->orderBy('name')
                ->pluck('name')
                ->all();
        }

        return $this->addPermissionStates($menu, $permissionNames);
    }

    /**
     * @param  array<int, string>  $permissionNames
     */
    private function addPermissionStates(MasterMenu $menu, array $permissionNames): MasterMenu
    {
        foreach (MasterMenu::PERMISSION_ACTIONS as $action) {
            $menu->setAttribute(
                "can_{$action}",
                in_array("{$menu->name}.{$action}", $permissionNames, true)
            );
        }

        $prefix = "{$menu->name}.";
        $menu->setAttribute(
            'custom_permissions',
            collect($permissionNames)
                ->filter(function (string $permissionName) use ($prefix): bool {
                    if (! str_starts_with($permissionName, $prefix)) {
                        return false;
                    }

                    $action = substr($permissionName, strlen($prefix));

                    return ! in_array($action, MasterMenu::PERMISSION_ACTIONS, true)
                        && preg_match('/^[A-Za-z0-9_-]+$/', $action) === 1;
                })
                ->map(fn(string $permissionName): array => [
                    'action' => substr($permissionName, strlen($prefix)),
                    'enabled' => true,
                ])
                ->values()
                ->all()
        );

        return $menu;
    }
    /**
     * Remove the specified master menu from storage.
     *
     * @throws Exception
     */
    public function delete(MasterMenu $masterMenu): bool
    {
        // Contoh validasi relasi sebelum dihapus (sesuaikan dengan relasi model Anda jika ada)
        // if ($masterMenu->someRelation()->exists()) {
        //     throw new Exception('Data cannot be deleted because it is still related to other data.');
        // }

        return DB::transaction(function () use ($masterMenu): bool {
            return (bool) $masterMenu->delete();
        });
    }
}
