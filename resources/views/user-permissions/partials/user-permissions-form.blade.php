<dialog class="dialog dialog-scrollable" id="user-permissionsModal" x-data="userPermissionModal(@js($permissionModules))" @edit-user-permissions-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title">Atur Permission User</h1>
        <button type="button" class="btn-close" commandfor="user-permissionsModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="user-permissionsForm" @submit.prevent="submitForm()">
            <div class="mb-3">
                <div class="fw-semibold" x-text="form.name"></div>
                <div class="text-muted small" x-text="form.email"></div>
                <div class="text-muted small" x-text="'NRK: ' + form.nrk"></div>
            </div>

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
                                                            :id="'user-permission-' + permission.id"
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
        <x-button-close commandfor="user-permissionsModal" command="close" />
        <x-button-save form="user-permissionsForm" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userPermissionModal', (modules = []) => ({
            permissionModules: modules,
            form: {
                id: '',
                name: '',
                email: '',
                nrk: '',
                permissions: []
            },
            errors: {},

            openEdit(user) {
                this.form = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    nrk: user.nrk,
                    permissions: [...user.permission_names]
                };
                this.errors = {};
                document.getElementById('user-permissionsModal').showModal();
            },

            submitForm() {
                this.errors = {};

                axios.put(
                    "{{ route('user-permissions.update', ['user' => '__ID__']) }}".replace('__ID__', this.form.id),
                    { permissions: [...this.form.permissions] }
                )
                    .then(response => {
                        document.getElementById('user-permissionsModal').close();
                        window.dispatchEvent(new CustomEvent('user-permissions-saved'));
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Berhasil',
                                message: response.data.message || 'Permission user berhasil diperbarui.',
                                type: 'success'
                            }
                        }));
                    })
                    .catch(error => {
                        if (error.response?.status === 422 && error.response.data.errors) {
                            for (const field in error.response.data.errors) {
                                this.errors[field] = error.response.data.errors[field][0];
                            }
                            return;
                        }

                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: error.response?.status === 403 ? 'Akses Ditolak' : 'Gagal Menyimpan',
                                message: error.response?.status === 403 ?
                                    'Anda tidak memiliki permission untuk melakukan aksi ini.' :
                                    error.response?.data?.message || 'Permission user tidak dapat diperbarui.',
                                type: 'error'
                            }
                        }));
                    });
            }
        }));
    });
</script>
