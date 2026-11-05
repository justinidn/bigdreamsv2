<dialog class="dialog dialog-scrollable" id="user-rolesModal" x-data="userRoleModal(@js($roles))" @edit-user-roles-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title">Atur Role User</h1>
        <button type="button" class="btn-close" commandfor="user-rolesModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="user-rolesForm" @submit.prevent="submitForm()">
            <div class="mb-3">
                <div class="fw-semibold" x-text="form.name"></div>
                <div class="text-muted small" x-text="form.email"></div>
                <div class="text-muted small" x-text="'NRK: ' + form.nrk"></div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Roles</label>
                <div class="row g-2">
                    <template x-for="role in roleOptions" :key="role.id">
                        <div class="col-12 col-md-6">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between gap-3">
                                <span x-text="role.name"></span>
                                <div class="form-field">
                                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                                        <input type="checkbox" role="switch" switch
                                            :id="'user-role-' + role.id"
                                            :value="role.id"
                                            x-model="form.roles">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="errors.roles || errors['roles.0']">
                    <div class="text-danger small mt-1" x-text="errors.roles || errors['roles.0']"></div>
                </template>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="user-rolesModal" command="close" />
        <x-button-save form="user-rolesForm" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userRoleModal', (roles = []) => ({
            roleOptions: roles,
            form: {
                id: '',
                name: '',
                email: '',
                nrk: '',
                roles: []
            },
            errors: {},

            openEdit(user) {
                this.form = {
                    id: user.id,
                    name: user.name,
                    email: user.email,
                    nrk: user.nrk,
                    roles: user.role_ids.map(Number)
                };
                this.errors = {};
                document.getElementById('user-rolesModal').showModal();
            },

            submitForm() {
                this.errors = {};

                axios.put(
                        "{{ route('user-roles.update', ['user' => '__ID__']) }}".replace('__ID__', this.form.id), {
                            roles: [...this.form.roles]
                        }
                    )
                    .then(response => {
                        document.getElementById('user-rolesModal').close();
                        window.dispatchEvent(new CustomEvent('user-roles-saved'));
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Berhasil',
                                message: response.data.message || 'Role user berhasil diperbarui.',
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
                                    error.response?.data?.message || 'Role user tidak dapat diperbarui.',
                                type: 'error'
                            }
                        }));
                    });
            }
        }));
    });
</script>