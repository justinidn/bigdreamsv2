<x-app-layout>
    <div x-data="userRolePage" x-on:user-roles-saved.window="refreshTable()">
        <x-table-data
            name="userRolesList"
            endpoint="{{ route('user-roles.index') }}"
            :pagination="true">

            <div class="row align-items-center g-2 mb-2">
                <label for="userRoleSearch" class="col-auto col-form-label" style="width: 110px;">Pencarian</label>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" id="userRoleSearch" x-model.trim="filters.search">
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
                <th class="text-center">Roles</th>

                @canany(['user-roles.update', 'user-roles.delete'])
                <th style="width: 120px;" class="text-center">Action</th>
                @endcanany
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.name"></td>
                <td x-text="item.email"></td>
                <td class="text-center" x-text="item.nrk"></td>
                <td>
                    <div class="d-flex flex-wrap justify-content-center gap-1">
                        <template x-for="role in item.roles" :key="role.id">
                            <span class="badge text-bg-light border" x-text="role.name"></span>
                        </template>
                        <span class="text-muted" x-show="!item.roles.length">Belum ada role</span>
                    </div>
                </td>
                @canany(['user-roles.update', 'user-roles.delete'])
                <td class="text-center">
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 shadow-none" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="menu">
                            @can('user-roles.update')
                            <button type="button" class="menu-item text-warning" @click="editItem(item)">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            @endcan
                            @can('user-roles.delete')
                            <button type="button" class="menu-item text-danger" @click="deleteItem(item.id)">
                                <i class="bi bi-trash3-fill"></i> Hapus
                            </button>
                            @endcan
                        </div>
                    </div>
                </td>
                @endcanany

            </x-slot>
        </x-table-data>

        @include('user-roles.partials.user-roles-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userRolePage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(item) {
                    axios.get("{{ route('user-roles.edit', ['user' => '__ID__']) }}".replace('__ID__', item.id))
                        .then(response => {
                            window.dispatchEvent(new CustomEvent('edit-user-roles-modal', {
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