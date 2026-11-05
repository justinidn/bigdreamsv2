<x-app-layout>
    <div x-data="userPage" x-on:user-saved.window="refreshTable()">
        <x-table-data name="usersList" endpoint="{{ route('users.index') }}" :pagination="true">

            <div class="row mb-3">
                <label for="inputEmail3" class="sm:col-auto col-form-label-sm">Email</label>
                <div class="sm:col-auto">
                    <input type="email" class="form-control form-control-sm" id="inputEmail3">
                </div>
            </div>
            <div class="row mb-3">
                <label for="name" class="sm:col-auto col-form-label-sm">Name</label>
                <div class="sm:col-auto">
                    <input type="text" class="form-control form-control-sm" id="search" x-model.trim="filters.search">
                </div>
            </div>
            <div class="row mb-3">
                <label for="department_cari" class="sm:col-auto col-form-label-sm">Department</label>
                <div class="sm:col-auto">
                    <select class="form-control form-control-sm" id="department_cari" x-model="filters.department_cari">
                        <option value="">Pilih Department</option>
                        @foreach($departments as $id => $name)
                        <option value="{{ $id }}">{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row mb-3">
                <label for="name" class="sm:col-auto col-form-label-sm">Status</label>
                <div class="sm:col-auto">
                    <select class="form-control form-control-sm" id="status" x-model="filters.status">
                        <option value="">Semua Status</option>
                        <option value="1">Active</option>
                        <option value="0">Inactive</option>
                    </select>
                </div>
            </div>
            <div class="row mb-3">
                <div class="sm:col-auto">
                    <x-button-search @click="fetchData(1)" />
                </div>
            </div>

            <div class="row mb-3">
                <div class="sm:col-auto">
                    <x-button-add @click="$dispatch('open-users-modal')" data-bs-toggle="dialog" data-bs-target="#usersModal" data-bs-backdrop="static" />
                </div>
            </div>



            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Nama</th>
                <th style="width: 200px;" class="text-center">Department</th>
                <th style="width: 140px;" class="text-center">Status</th>
                <th style="width: 140px;" class="text-center">Can Login</th>
                <th style="width: 120px;" class="text-center">Action</th>

            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.name"></td>
                <td class="text-center" x-text="item.department?.department_alias || 'N/A'"></td>
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