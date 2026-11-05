<dialog class="dialog dialog-scrollable" id="departmentsModal" x-data="departmentModal()" @open-departments-modal.window="openAdd()" @edit-departments-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit Department' : 'Tambah Department Baru'"></h1>
        <button type="button" class="btn-close" commandfor="departmentsModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="departmentsForm" @submit.prevent="submitForm()">
            <div class="mb-3">
                <label for="departments_name" class="form-label">Nama Department</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.department_name }" id="departments_name" x-model="form.department_name" required>
                <template x-if="errors.department_name">
                    <div class="invalid-feedback" x-text="errors.department_name"></div>
                </template>
            </div>

            <div class="mb-3">
                <label for="departments_alias" class="form-label">Alias</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.department_alias }" id="departments_alias" x-model="form.department_alias" required>
                <template x-if="errors.department_alias">
                    <div class="invalid-feedback" x-text="errors.department_alias"></div>
                </template>
            </div>

            <div class="mb-3" x-show="isEdit">
                <label class="form-label">Status</label>
                <div class="form-field">
                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                        <input type="checkbox" value="1" id="switchCustomWidth" role="switch" switch x-model="form.is_active">
                    </div>
                    <label for="switchCustomWidth">Active</label>
                </div>
            </div>
        </form>
    </div>



    <div class="dialog-footer">
        <x-button-close commandfor="departmentsModal" command="close" />
        <x-button-save form="departmentsForm" />
    </div>

</dialog>
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('departmentModal', () => ({
            isEdit: false,
            originalForm: {},
            form: {
                id: '',
                department_name: '',
                department_alias: '',
                is_active: true
            },
            errors: {}, // <-- Tambahkan state untuk menampung error per field

            openAdd() {
                this.isEdit = false;
                this.form = {
                    id: '',
                    department_name: '',
                    department_alias: '',
                    is_active: true
                };
                this.originalForm = {};
                this.errors = {}; // Reset error saat buka modal
                document.getElementById('departmentsModal').showModal();
            },

            openEdit(item) {
                this.isEdit = true;
                this.form = {
                    id: item.id,
                    department_name: item.department_name,
                    department_alias: item.department_alias,
                    is_active: Boolean(Number(item.is_active))
                };
                this.originalForm = {
                    ...this.form
                };
                this.errors = {};
                document.getElementById('departmentsModal').showModal();
            },

            submitForm() {
                this.errors = {};

                const updateData = Object.fromEntries(
                    Object.entries(this.form)
                    .filter(([field, value]) => field !== 'id' && value !== this.originalForm[field])
                );

                if (this.isEdit && Object.keys(updateData).length === 0) {
                    window.dispatchEvent(new CustomEvent('show-toast', {
                        detail: {
                            title: 'Informasi',
                            message: 'Tidak ada perubahan data yang dilakukan.',
                            type: 'success'
                        }
                    }));
                    document.getElementById('departmentsModal').close();
                    return;
                }

                const request = this.isEdit ?
                    axios.put(
                        "{{ route('departments.update', ['department' => '__DEPARTMENT_ID__']) }}"
                        .replace('__DEPARTMENT_ID__', this.form.id),
                        updateData
                    ) :
                    axios.post("{{ route('departments.store') }}", this.form);

                request
                    .then(response => {
                        document.getElementById('departmentsModal').close();
                        window.dispatchEvent(new CustomEvent('department-saved'));

                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Berhasil',
                                message: response.data.message || 'Departemen berhasil disimpan.',
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