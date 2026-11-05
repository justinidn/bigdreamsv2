<dialog class="dialog dialog-scrollable" id="rolesModal" data-bs-backdrop="static" x-data="roleModal(@js($permissionModules))" @open-roles-modal.window="openAdd()" @edit-roles-modal.window="openEdit($event.detail)" @cancel.prevent>
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit Role' : 'Tambah Role Baru'"></h1>
        <button type="button" class="btn-close" commandfor="rolesModal" command="close" aria-label="Close"></button>
    </div>

    <div class="dialog-body">
        <form id="rolesForm" @submit.prevent="submitForm()">
            <!-- Input Nama Role -->
            <div class="mb-3">
                <label for="name" class="form-label">Nama Role</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.name }" id="name" x-model="form.name" required>
                <template x-if="errors.name">
                    <div class="invalid-feedback" x-text="errors.name"></div>
                </template>
            </div>

            <!-- Permission per Module -->
            <div class="mb-3">
                <label class="form-label fw-bold">Permission per Module</label>
                <div class="row g-2">
                    <template x-for="module in permissionModules" :key="module.key">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 h-100">
                                <div class="fw-semibold mb-2" x-text="module.label"></div>
                                <div class="row g-2">
                                    <template x-for="permission in module.permissions" :key="permission.name">
                                        <div class="col-6">
                                            <div class="d-flex align-items-center justify-content-between gap-2">
                                                <span x-text="permission.label" class="small"></span>
                                                <div class="form-field">
                                                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                                                        <input type="checkbox" role="switch" switch
                                                            :id="'permission-' + permission.id"
                                                            :value="permission.name"
                                                            x-model="form.permissions">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="errors.permissions || errors['permissions.0']">
                    <div class="text-danger small mt-1" x-text="errors.permissions || errors['permissions.0']"></div>
                </template>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="rolesModal" command="close" />
        <x-button-save form="rolesForm" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('roleModal', (initialModules = []) => ({
            isEdit: false,
            permissionModules: initialModules,
            form: {
                id: '',
                name: '',
                permissions: []
            },
            errors: {},

            openAdd() {
                this.isEdit = false;
                this.form = {
                    id: '',
                    name: '',
                    permissions: []
                };
                this.errors = {};
                document.getElementById('rolesModal').showModal();
            },

            openEdit(item) {
                this.isEdit = true;
                this.form = {
                    id: item.id,
                    name: item.name,
                    permissions: item.enabled_permissions || []
                };
                this.errors = {};
                document.getElementById('rolesModal').showModal();
            },

            submitForm() {
                this.errors = {};

                const formData = {
                    name: this.form.name,
                    permissions: [...this.form.permissions]
                };

                const request = this.isEdit ?
                    axios.put(
                        "{{ route('roles.update', ['role' => '__ID__']) }}"
                        .replace('__ID__', this.form.id),
                        formData
                    ) :
                    axios.post("{{ route('roles.store') }}", formData);

                request
                    .then(response => {
                        document.getElementById('rolesModal').close();
                        window.dispatchEvent(new CustomEvent('role-saved'));

                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Berhasil',
                                message: response.data.message || 'Data berhasil disimpan.',
                                type: 'success'
                            }
                        }));
                    })
                    .catch(error => {
                        if (error.response) {
                            if (error.response.status === 422 && error.response.data.errors) {
                                const backendErrors = error.response.data.errors;
                                for (const field in backendErrors) {
                                    this.errors[field] = backendErrors[field][0];
                                }
                            } else if (error.response.status === 403) {
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: {
                                        title: 'Akses Ditolak',
                                        message: 'Anda tidak memiliki permission untuk melakukan aksi ini.',
                                        type: 'error'
                                    }
                                }));
                            } else {
                                window.dispatchEvent(new CustomEvent('show-toast', {
                                    detail: {
                                        title: 'Kesalahan Server',
                                        message: error.response.data.message || 'Terjadi kesalahan pada server.',
                                        type: 'error'
                                    }
                                }));
                            }
                        } else {
                            window.dispatchEvent(new CustomEvent('show-toast', {
                                detail: {
                                    title: 'Koneksi Gagal',
                                    message: 'Tidak dapat terhubung ke server.',
                                    type: 'error'
                                }
                            }));
                        }
                    });
            }
        }));
    });
</script>