<x-app-layout>
    <div x-data="departmentPage" x-on:department-saved.window="refreshTable()">
        <x-table-data name="departmentsList" endpoint="{{ route('departments.index') }}" :pagination="true">

            <div class="d-flex align-items-center gap-2 w-100">
                <label class="form-control form-control-sm form-adorn flex-grow-1 mb-0">
                    <div class="form-adorn-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="search" class="form-ghost w-100" placeholder="Search Department" x-model.trim="filters.search" @input.debounce.400ms="applyFilters()">
                </label>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="d-inline-block rounded-3" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Advanced Search">
                        <button class="btn-solid theme-primary btn-sm" type="button" data-bs-toggle="drawer" data-bs-target="#drawerRight" aria-controls="drawerRight"><i class="bi bi-funnel-fill"></i></button>
                    </span>
                    @can('departments.create')
                    <x-button-add @click="$dispatch('open-departments-modal')" data-bs-toggle="dialog" data-bs-target="#departmentsModal" data-bs-backdrop="static" />
                    @endcan
                </div>
            </div>

            <dialog class="drawer drawer-end" data-bs-backdrop="false" data-bs-scroll="true" tabindex="-1" id="drawerRight" aria-labelledby="drawerRightLabel">
                <div class="drawer-header">
                    <h5 class="drawer-title" id="drawerRightLabel">Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
                </div>
                <div class="drawer-body">
                    <div class="mb-1">
                        <label for="status" class="form-label">Status</label>
                        <select class="form-control form-control-sm" id="status" x-model="filters.status">
                            <option value="">Semua Status</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>

                    <x-button-search @click="applyFilters()" />
                </div>
            </dialog>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Department Name</th>
                <th class="text-center">Department Alias</th>
                <th style="width: 140px;" class="text-center">Status</th>
                @canany(['departments.update', 'departments.delete'])
                <th style="width: 120px;" class="text-center">Action</th>
                @endcanany
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.department_name"></td>
                <td x-text="item.department_alias"></td>
                <td class="text-center">
                    <span class="badge rounded-pill px-3 py-1" :class="item.is_active ? 'bg-primary' : 'bg-danger'" x-text="item.is_active ? 'Aktif' : 'Non-Aktif'"></span>
                </td>
                @canany(['departments.update', 'departments.delete'])
                <td class="text-center">
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 shadow-none" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="menu">
                            @can('departments.update')
                            <button type="button" class="menu-item text-warning" @click="editItem(item)">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            @endcan
                            @can('departments.delete')
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

        {{-- Include Modal Form --}}
        @include('departments.partials.departments-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('departmentPage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(item) {
                    window.dispatchEvent(new CustomEvent('edit-departments-modal', {
                        detail: item
                    }));
                },
                deleteItem(id) {
                    if (confirm('Yakin ingin menghapus data ini?')) {
                        axios.delete("{{ route('departments.destroy', ['department' => '__ID__']) }}".replace('__ID__', id))
                            .then(response => {
                                window.dispatchEvent(new CustomEvent('department-saved'));
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