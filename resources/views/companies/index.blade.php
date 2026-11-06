<x-app-layout>
    <div x-data="companyPage" x-on:company-saved.window="refreshTable()">
        <x-table-data name="companiesList" endpoint="{{ route('companies.index') }}" :pagination="true">

            <div class="d-flex align-items-center gap-2 w-100">
                <label class="form-control form-control-sm form-adorn flex-grow-1 mb-0">
                    <div class="form-adorn-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="search" class="form-ghost w-100" placeholder="Search Company" x-model.trim="filters.search" @input.debounce.400ms="applyFilters()">
                </label>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="d-inline-block rounded-3" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Advanced Search">
                        <button class="btn-solid theme-primary btn-sm" type="button" data-bs-toggle="drawer" data-bs-target="#drawerRight" aria-controls="drawerRight"><i class="bi bi-funnel-fill"></i></button>
                    </span>
                    @can('companies.create')
                    <x-button-add @click="$dispatch('open-companies-modal')" data-bs-toggle="dialog" data-bs-target="#companiesModal" data-bs-backdrop="static" />
                    @endcan
                </div>
            </div>

            <dialog class="drawer drawer-end" data-bs-backdrop="false" data-bs-scroll="true" tabindex="-1" id="drawerRight" aria-labelledby="drawerRightLabel">
                <div class="drawer-header">
                    <h5 class="drawer-title" id="drawerRightLabel">Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
                </div>
                <div class="drawer-body">
                    <x-button-search @click="applyFilters()" />
                </div>
            </dialog>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Company Code</th>
                <th class="text-center">Company Name</th>
                @canany(['companies.update', 'companies.delete'])
                <th style="width: 120px;" class="text-center">Action</th>
                @endcanany
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.company_code"></td>
                <td x-text="item.company_name"></td>
                @canany(['companies.update', 'companies.delete'])
                <td class="text-center">
                    <div class="position-relative">
                        <button class="btn btn-sm btn-light border-0 px-2 py-1 shadow-none" type="button" data-bs-toggle="menu" aria-expanded="false">
                            <i class="bi bi-three-dots"></i>
                        </button>
                        <div class="menu">
                            @can('companies.update')
                            <button type="button" class="menu-item text-warning" @click="editItem(item)">
                                <i class="bi bi-pencil-square"></i> Edit
                            </button>
                            @endcan
                            @can('companies.delete')
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
        @include('companies.partials.companies-form')
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('companyPage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                },
                editItem(item) {
                    window.dispatchEvent(new CustomEvent('edit-companies-modal', {
                        detail: item
                    }));
                },
                deleteItem(id) {
                    if (confirm('Yakin ingin menghapus data ini?')) {
                        axios.delete("{{ route('companies.destroy', ['company' => '__ID__']) }}".replace('__ID__', id))
                            .then(response => {
                                window.dispatchEvent(new CustomEvent('company-saved'));
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