<x-app-layout>
    <div x-data="userPermissionPage" x-on:user-permissions-saved.window="refreshTable()">
        <x-table-data
            name="userPermissionsList"
            endpoint="{{ route('user-permissions.index') }}"
            :pagination="true">

            <div class="row align-items-center g-2 mb-2">
                <label for="userPermissionSearch" class="col-auto col-form-label" style="width: 110px;">Pencarian</label>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" id="userPermissionSearch" x-model.trim="filters.search">
                </div>
            </div>

            <div class="col-auto d-flex align-items-center gap-2 mb-3">
                <x-button-search @click="fetchData(1)" />
            </div>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Nama User</th>
                <th class="text-center">Email</th>
                <th style="width: 160px;" class="text-center">NRK</th>
                <th class="text-center">Direct Permissions</th>
                @can('user-permissions.update')
                <th style="width: 120px;" class="text-center">Action</th>
                @endcan
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.name"></td>
                <td x-text="item.email"></td>
                <td class="text-center" x-text="item.nrk"></td>
                <td>
                    <div class="d-flex flex-wrap justify-content-center gap-1">
                        <template x-for="permission in item.permission_names" :key="permission">
                            <span class="badge text-bg-light border" x-text="permission"></span>
                        </template>
                        <span class="text-muted" x-show="!item.permission_names.length">Belum ada permission langsung</span>
                    </div>
                </td>
                @can('user-permissions.update')
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-warning" @click="editItem(item)">
                        <i class="bi bi-pencil-square"></i> Atur
                    </button>
                </td>
                @endcan
            </x-slot>
        </x-table-data>

        @include('user-permissions.partials.user-permissions-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userPermissionPage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(user) {
                    axios.get("{{ route('user-permissions.edit', ['user' => '__ID__']) }}".replace('__ID__', user.id))
                        .then(response => {
                            window.dispatchEvent(new CustomEvent('edit-user-permissions-modal', {
                                detail: response.data.data
                            }));
                        })
                        .catch(error => {
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: {
                                    title: 'Gagal Memuat User',
                                    message: error.response?.data?.message || 'Data user tidak dapat dimuat.',
                                    type: 'error'
                                }
                            }));
                        });
                }
            }));
        });
    </script>
</x-app-layout>
