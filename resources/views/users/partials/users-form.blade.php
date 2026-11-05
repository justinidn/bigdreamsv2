<dialog class="dialog dialog-scrollable" id="usersModal" x-data="userModal()" @open-users-modal.window="openAdd()" @edit-users-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit User' : 'Tambah User Baru'"></h1>
        <button type="button" class="btn-close" commandfor="usersModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="usersForm" @submit.prevent="submitForm()">
            <div class="mb-3">
                <label for="name" class="form-label">Nama User</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.name }" id="name" x-model="form.name" required>
                <template x-if="errors.name">
                    <div class="invalid-feedback" x-text="errors.name"></div>
                </template>
            </div>
            <div class="mb-3">
                <label for="nrk" class="form-label">NRK</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.nrk }" id="nrk" x-model="form.nrk" required>
                <template x-if="errors.nrk">
                    <div class="invalid-feedback" x-text="errors.nrk"></div>
                </template>
            </div>
            <div class="mb-3">
                <label for="department_id" class="form-label">Department</label>
                <select class="form-control form-control-sm" :class="{ 'is-invalid': errors.department_id }" id="department_id" x-model="form.department_id" required>
                    <option value="">Pilih Department</option>
                    @foreach($departments as $id => $name)
                    <option value="{{ $id }}">{{ $name }}</option>
                    @endforeach
                </select>
                <template x-if="errors.department_id">
                    <div class="invalid-feedback" x-text="errors.department_id"></div>
                </template>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control form-control-sm" :class="{ 'is-invalid': errors.email }" id="email" x-model="form.email" required>
                <template x-if="errors.email">
                    <div class="invalid-feedback" x-text="errors.email"></div>
                </template>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.password }" id="password" x-model="form.password">
                <template x-if="errors.password">
                    <div class="invalid-feedback" x-text="errors.password"></div>
                </template>
            </div>
            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.password_confirmation }" id="password_confirmation" x-model="form.password_confirmation">
                <template x-if="errors.password_confirmation">
                    <div class="invalid-feedback" x-text="errors.password_confirmation"></div>
                </template>
            </div>

            <div class="mb-3" x-show="isEdit">
                <label class="form-label">Status</label>
                <div class="form-field">
                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                        <input type="checkbox" value="1" id="switchActiveUser" role="switch" switch x-model="form.is_active">
                    </div>
                    <label for="switchActiveUser">Active</label>
                </div>
            </div>
            <div class="mb-3" x-show="isEdit">
                <label class="form-label">Can Login</label>
                <div class="form-field">
                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                        <input type="checkbox" value="1" id="switchCanLogin" role="switch" switch x-model="form.can_login">
                    </div>
                    <label for="switchCanLogin">Yes</label>
                </div>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="usersModal" command="close" />
        <x-button-save form="usersForm" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('userModal', () => ({
            isEdit: false,
            originalForm: {},
            form: {
                id: '',
                name: '',
                nrk: '',
                department_id: '',
                'email': '',
                password: '',
                password_confirmation: '',
                is_active: true,
                can_login: false
            },
            errors: {},

            openAdd() {
                this.isEdit = false;
                this.form = {
                    id: '',
                    name: '',
                    nrk: '',
                    department_id: '',
                    'email': '',
                    password: '',
                    password_confirmation: '',
                    is_active: true,
                    can_login: false
                };
                this.originalForm = {};
                this.errors = {};
                document.getElementById('usersModal').showModal();
            },

            openEdit(item) {
                this.isEdit = true;
                this.form = {
                    id: item.id,
                    name: item.name,
                    nrk: item.nrk,
                    department_id: item.department_id,
                    'email': item.email,
                    password: '',
                    password_confirmation: '',
                    is_active: Boolean(Number(item.is_active)),
                    can_login: Boolean(Number(item.can_login))
                };
                this.originalForm = {
                    ...this.form
                };
                this.errors = {};
                document.getElementById('usersModal').showModal();
            },

            submitForm() {
                this.errors = {};

                const formData = {
                    ...this.form
                };

                const request = this.isEdit ?
                    axios.put(
                        "{{ route('users.update', ['user' => '__ID__']) }}"
                        .replace('__ID__', this.form.id),
                        formData
                    ) :
                    axios.post("{{ route('users.store') }}", formData);

                request
                    .then(response => {
                        document.getElementById('usersModal').close();
                        window.dispatchEvent(new CustomEvent('user-saved'));

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