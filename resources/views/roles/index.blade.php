<x-app-layout>
    <div x-data="rolePage" x-on:role-saved.window="refreshTable()">
        <x-table-data
            name="rolesList"
            endpoint="{{ route('roles.index') }}"
            :pagination="true">

            <div class="row align-items-center g-2 mb-2">
                <label for="search" class="col-auto col-form-label" style="width: 110px;">Pencarian</label>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" id="search" x-model.trim="filters.search">
                </div>
            </div>

            <!-- <div class="row align-items-center g-2 mb-2">
                <label for="status" class="col-auto col-form-label" style="width: 110px;">Status</label>
                <div class="col">
                    <select class="form-control form-control-sm" id="status" x-model="filters.status">
                        <option value="">Semua Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div> -->

            <div class="col-auto d-flex align-items-center gap-2 mb-3">
                <x-button-search @click="fetchData(1)" />
                <x-button-add @click="$dispatch('open-roles-modal')" data-bs-toggle="dialog" data-bs-target="#rolesModal" data-bs-backdrop="static" />
            </div>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Nama</th>
                <th style="width: 120px;" class="text-center">Action</th>
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.name"></td>

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
        @include('roles.partials.roles-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('rolePage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(item) {
                    axios.get("{{ route('roles.edit', ['role' => '__ID__']) }}".replace('__ID__', item.id))
                        .then(response => {
                            window.dispatchEvent(new CustomEvent('edit-roles-modal', {
                                detail: response.data.data
                            }));
                        })
                        .catch(error => {
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: {
                                    title: 'Gagal Memuat Role',
                                    message: error.response?.data?.message || 'Data role tidak dapat dimuat.',
                                    type: 'error'
                                }
                            }));
                        });
                },
                deleteItem(id) {
                    if (confirm('Yakin ingin menghapus data ini?')) {
                        axios.delete("{{ route('roles.destroy', ['role' => '__ID__']) }}".replace('__ID__', id))
                            .then(response => {
                                window.dispatchEvent(new CustomEvent('role-saved'));
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