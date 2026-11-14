<x-app-layout>
    <div x-data="userPage" x-on:user-saved.window="refreshTable()">
        <x-table-data name="usersList" endpoint="{{ route('users.index') }}" :pagination="true">

            <div class="d-flex align-items-center gap-2 w-100">
                <label class="form-control form-control-sm form-adorn flex-grow-1 mb-0">
                    <div class="form-adorn-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="search" class="form-ghost w-100" placeholder="Search Name,NRK" x-model.trim="filters.search" @input.debounce.400ms="applyFilters()">
                </label>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="d-inline-block rounded-3" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Advanced Search">
                        <button class="btn-solid theme-primary btn-sm" type="button" data-bs-toggle="drawer" data-bs-target="#drawerRight" aria-controls="drawerRight"><i class="bi bi-funnel-fill"></i></button>
                    </span>
                    <x-button-add @click="$dispatch('open-users-modal')" data-bs-toggle="dialog" data-bs-target="#usersModal" data-bs-backdrop="static" />
                </div>
            </div>
            <dialog class="drawer drawer-end" data-bs-backdrop="false" data-bs-scroll="true" tabindex="-1" id="drawerRight" aria-labelledby="drawerRightLabel">
                <div class="drawer-header">
                    <h5 class="drawer-title" id="drawerRightLabel">Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
                </div>
                <div class="drawer-body">
                    <div class="mb-1">
                        <label for="department_cari" class="form-label">Department</label>
                        <select class="form-control form-control-sm" id="department_cari" x-model="filters.department_cari">
                            <option value="">Pilih Department</option>
                            @foreach($departments as $id => $name)
                            <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="mb-1">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control form-control-sm" id="status" x-model="filters.status">
                            <option value="">Pilih Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <x-button-search @click="applyFilters()" />
                </div>
            </dialog>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Nama</th>
                <th style="width: 140px;" class="text-center">Status</th>
                <th style="width: 140px;" class="text-center">Can Login</th>
                <th style="width: 120px;" class="text-center">Action</th>

            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.name"></td>
                <td class="text-center">
                    <span class="badge rounded-pill px-3 py-1" :class="item.is_active ? 'bg-primary' : 'bg-danger'" x-text="item.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                </td>
                <td class="text-center">
                    <template x-if="item.can_login">
                        <i class="bi bi-check-circle-fill text-success fs-5" title="Ya"></i>
                    </template>
                    <template x-if="!item.can_login">
                        <i class="bi bi-x-circle-fill text-danger fs-5" title="Tidak"></i>
                    </template>
                </td>
                <td class="text-center">
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 shadow-none" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="menu">
                            <button type="button" class="menu-item text-warning" @click="editItem(item)">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            <button type="button" class="menu-item text-danger" @click="deleteItem(item.id)">
                                <i class="bi bi-trash3-fill"></i> Hapus
                            </button>
                        </div>
                    </div>
                </td>
            </x-slot>
        </x-table-data>

        {{-- Include Modal Form --}}
        @include('users.partials.users-form')
    </div>


    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('userPage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(item) {
                    window.dispatchEvent(new CustomEvent('edit-users-modal', {
                        detail: item
                    }));
                },
                deleteItem(id) {
                    if (confirm('Yakin ingin menghapus data ini?')) {
                        axios.delete("{{ route('users.destroy', ['user' => '__ID__']) }}".replace('__ID__', id))
                            .then(response => {
                                window.dispatchEvent(new CustomEvent('user-saved'));
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: {
                                        title: 'Berhasil',
                                        message: response.data.message || 'Data berhasil dihapus.',
                                        type: 'success'
                                    }
                                }));
                            })
                            .catch(error => {
                                let errorMsg = error.response?.data?.message || 'Terjadi kesalahan pada server.';
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: {
                                        title: 'Peringatan',
                                        message: errorMsg,
                                        type: 'error'
                                    }
                                }));
                            });
                    }
                }
            }));
        });
    </script>
</x-app-layout>