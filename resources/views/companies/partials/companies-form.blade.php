<dialog class="dialog dialog-scrollable" id="companiesModal" x-data="companyModal()" @open-companies-modal.window="openAdd()" @edit-companies-modal.window="openEdit($event.detail)">
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit Company' : 'Tambah Company Baru'"></h1>
        <button type="button" class="btn-close" commandfor="companiesModal" command="close" aria-label="Close"></button>
    </div>
    <div class="dialog-body">
        <form id="companiesForm" @submit.prevent="submitForm()">
            <div class="mb-3">
                <label for="company_name" class="form-label">Company_code</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.company_name }" id="company_name" x-model="form.company_code" required>
                <template x-if="errors.company_code">
                    <div class="invalid-feedback" x-text="errors.company_code"></div>
                </template>
            </div>

            <div class="mb-3">
                <label for="company_name" class="form-label">Nama Company</label>
                <input type="text" class="form-control form-control-sm" :class="{ 'is-invalid': errors.company_name }" id="company_name" x-model="form.company_name" required>
                <template x-if="errors.company_name">
                    <div class="invalid-feedback" x-text="errors.company_name"></div>
                </template>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="companiesModal" command="close" />
        <x-button-save form="companiesForm" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('companyModal', () => ({
            isEdit: false,
            originalForm: {},
            form: {
                id: '',
                name: '',
                is_active: true
            },
            errors: {},

            openAdd() {
                this.isEdit = false;
                this.form = {
                    id: '',
                    name: '',
                    is_active: true
                };
                this.originalForm = {};
                this.errors = {};
                document.getElementById('companiesModal').showModal();
            },

            openEdit(item) {
                this.isEdit = true;
                this.form = {
                    id: item.id,
                    name: item.name,
                    is_active: Boolean(Number(item.is_active))
                };
                this.originalForm = {
                    ...this.form
                };
                this.errors = {};
                document.getElementById('companiesModal').showModal();
            },

            submitForm() {
                this.errors = {};

                // Menggunakan seluruh data dari this.form secara utuh
                const formData = {
                    ...this.form
                };

                const request = this.isEdit ?
                    axios.put(
                        "{{ route('companies.update', ['company' => '__ID__']) }}"
                        .replace('__ID__', this.form.id),
                        formData
                    ) :
                    axios.post("{{ route('companies.store') }}", formData);

                request
                    .then(response => {
                        document.getElementById('companiesModal').close();
                        window.dispatchEvent(new CustomEvent('company-saved'));

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