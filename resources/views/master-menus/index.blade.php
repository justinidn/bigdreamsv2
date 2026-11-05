<x-app-layout>
    <div x-data="masterMenuPage(
        @js(auth()->user()->can('master-menus.update'))
    )" x-on:master-menu-saved.window="refreshTable()">
        <x-table-data
            name="masterMenusList"
            endpoint="{{ route('master-menus.index') }}"
            :pagination="true">

            <div class="row align-items-center g-2 mb-2">
                <label for="masterMenuSearch" class="col-auto col-form-label" style="width: 110px;">Menus</label>
                <div class="col">
                    <input type="text" class="form-control form-control-sm" id="masterMenuSearch" x-model.trim="filters.search">
                </div>
            </div>

            <div class="col-auto d-flex align-items-center gap-2 mb-3">
                <x-button-search @click="fetchData(1)" />
                @can('master-menus.create')
                <x-button-add @click="$dispatch('open-master-menu-modal')" />
                @endcan
            </div>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-start">Module</th>
                @canany(['master-menus.update', 'master-menus.delete'])
                <th style="width: 120px;" class="text-center">Action</th>
                @endcanany
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td class="text-start">
                    <span class="fw-semibold" x-text="item.display_name"></span>
                    <span class="text-muted small ms-1" x-text="'(' + item.name + ')'"></span>
                </td>
                @canany(['master-menus.update', 'master-menus.delete'])
                <td class="text-center">
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 shadow-none" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="menu">
                            @can('master-menus.update')
                            <button type="button" class="menu-item text-warning" @click="editItem(item)">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            @endcan
                            @can('master-menus.delete')
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

        @include('master-menus.partials.master-menu-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('masterMenuPage', (canUpdatePermissions) => ({
                canUpdatePermissions,

                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },

                editItem(menu) {
                    if (!this.canUpdatePermissions) {
                        return;
                    }

                    axios.get("{{ route('master-menus.edit', ['masterMenu' => '__ID__']) }}".replace('__ID__', menu.id))
                        .then(response => {
                            window.dispatchEvent(new CustomEvent('edit-master-menu-modal', {
                                detail: response.data.data
                            }));
                        })
                        .catch(error => {
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: {
                                    title: error.response?.status === 403 ? 'Akses Ditolak' : 'Gagal Memuat Module',
                                    message: error.response?.data?.message || 'Data module tidak dapat dimuat.',
                                    type: 'error'
                                }
                            }));
                        });
                },
                deleteItem(id) {
                    if (confirm('Yakin ingin menghapus data ini?')) {
                        axios.delete("{{ route('master-menus.destroy', ['masterMenu' => '__ID__']) }}".replace('__ID__', id))
                            .then(response => {
                                window.dispatchEvent(new CustomEvent('master-menu-saved'));
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