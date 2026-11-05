<dialog class="dialog dialog-scrollable" id="master-menu-modal" data-bs-backdrop="static" x-data="masterMenuModal()" @open-master-menu-modal.window="openAdd()" @edit-master-menu-modal.window="openEdit($event.detail)" @cancel.prevent>
    <div class="dialog-header">
        <h1 class="dialog-title" x-text="isEdit ? 'Edit Permission Module' : 'Tambah Module Baru'"></h1>
        <button type="button" class="btn-close" commandfor="master-menu-modal" command="close" aria-label="Close"></button>
    </div>

    <div class="dialog-body">
        <form id="master-menu-form" @submit.prevent="submitForm()">
            <div class="mb-3">
                <template x-if="!isEdit">
                    <div>
                        <label for="masterMenuName" class="form-label">Module key</label>
                        <input
                            type="text"
                            class="form-control form-control-sm"
                            :class="{ 'is-invalid': errors.name }"
                            id="masterMenuName"
                            maxlength="50"
                            x-model.trim="form.name"
                            required>
                        <template x-if="errors.name">
                            <div class="invalid-feedback" x-text="errors.name"></div>
                        </template>
                    </div>
                </template>
                <template x-if="isEdit">
                    <div>
                        <label class="form-label">Module key</label>
                        <div class="fw-semibold" x-text="form.name"></div>
                    </div>
                </template>
            </div>

            <div class="mb-3">
                <label for="masterMenuDisplayName" class="form-label">Display name</label>
                <input
                    type="text"
                    class="form-control form-control-sm"
                    :class="{ 'is-invalid': errors.display_name }"
                    id="masterMenuDisplayName"
                    maxlength="50"
                    x-model.trim="form.display_name"
                    required>
                <template x-if="errors.display_name">
                    <div class="invalid-feedback" x-text="errors.display_name"></div>
                </template>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Default Permissions</label>
                <div class="row g-2">
                    @foreach (['view', 'create', 'update', 'delete', 'export'] as $action)
                    <!-- Diubah dari col-12 col-md-6 menjadi col-6 agar selalu 2 kolom -->
                    <div class="col-6">
                        <div class="border rounded p-3 d-flex align-items-center justify-content-between gap-3">
                            <span>{{ ucfirst($action) }}</span>
                            <div class="form-field">
                                <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                                    <input
                                        type="checkbox"
                                        role="switch"
                                        switch
                                        id="master-menu-{{ $action }}"
                                        x-model="form.can_{{ $action }}">
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-bold">Permission custom</label>
                <div class="d-flex flex-column gap-2">
                    <template x-if="showCustomPermissionInput">
                        <div>
                            <input
                                type="text"
                                class="form-control form-control-sm"
                                :class="{ 'is-invalid': errors.custom_permission }"
                                id="customPermissionName"
                                maxlength="50"
                                placeholder="Contoh: approve"
                                x-model.trim="customPermissionName">
                            <template x-if="errors.custom_permission">
                                <div class="invalid-feedback" x-text="errors.custom_permission"></div>
                            </template>
                            <div class="form-text">Nama ini akan ditambahkan ke module sebagai permission baru.</div>
                        </div>
                    </template>
                    <button type="button" class="btn btn-sm btn-outline-primary align-self-start" @click="showCustomPermissionInput = true">
                        <i class="bi bi-plus-circle"></i> Tambah
                    </button>
                </div>
                <div class="row g-2 mt-1">
                    <template x-for="(permission, index) in form.custom_permissions" :key="permission.action">
                        <div class="col-6">
                            <div class="border rounded p-3 d-flex align-items-center justify-content-between gap-3">
                                <span x-text="permission.action"></span>
                                <div class="form-field">
                                    <div class="switch" style="--bs-switch-width: 2.75rem; --bs-switch-indicator-width: 1.5rem;">
                                        <input
                                            type="checkbox"
                                            role="switch"
                                            switch
                                            :id="'custom-permission-' + index"
                                            x-model="permission.enabled">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
                <template x-if="errors.custom_permissions || errors['custom_permissions.0.action'] || errors['custom_permissions.0.enabled']">
                    <div class="text-danger small mt-1" x-text="errors.custom_permissions || errors['custom_permissions.0.action'] || errors['custom_permissions.0.enabled']"></div>
                </template>
            </div>
        </form>
    </div>

    <div class="dialog-footer">
        <x-button-close commandfor="master-menu-modal" command="close" />
        <x-button-save form="master-menu-form" />
    </div>
</dialog>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('masterMenuModal', () => ({
            isEdit: false,
            form: {},
            customPermissionName: '',
            showCustomPermissionInput: false,
            errors: {},

            openAdd() {
                this.isEdit = false;
                this.form = {
                    name: '',
                    display_name: '',
                    can_view: false,
                    can_create: false,
                    can_update: false,
                    can_delete: false,
                    can_export: false,
                    custom_permissions: []
                };
                this.customPermissionName = '';
                this.showCustomPermissionInput = false;
                this.errors = {};
                document.getElementById('master-menu-modal').showModal();
            },

            openEdit(menu) {
                this.isEdit = true;
                this.form = {
                    id: menu.id,
                    name: menu.name,
                    display_name: menu.display_name,
                    can_view: Boolean(menu.can_view),
                    can_create: Boolean(menu.can_create),
                    can_update: Boolean(menu.can_update),
                    can_delete: Boolean(menu.can_delete),
                    can_export: Boolean(menu.can_export),
                    custom_permissions: menu.custom_permissions.map(permission => ({
                        ...permission
                    }))
                };
                this.customPermissionName = '';
                this.showCustomPermissionInput = false;
                this.errors = {};
                document.getElementById('master-menu-modal').showModal();
            },

            prepareCustomPermissionForSave() {
                const action = this.customPermissionName.trim().toLowerCase();

                if (!this.showCustomPermissionInput || !action) {
                    return true;
                }

                if (!/^[a-z0-9_-]+$/.test(action)) {
                    this.errors.custom_permission = 'Gunakan huruf kecil, angka, tanda hubung, atau garis bawah.';
                    return false;
                }

                if (['view', 'create', 'update', 'delete', 'export'].includes(action)) {
                    this.errors.custom_permission = 'Nama tersebut sudah termasuk permission bawaan.';
                    return false;
                }

                const existingPermission = this.form.custom_permissions.find(permission => permission.action === action);
                if (existingPermission) {
                    existingPermission.enabled = true;
                } else {
                    this.form.custom_permissions.push({
                        action,
                        enabled: true
                    });
                }

                return true;
            },

            submitForm() {
                this.errors = {};

                if (!this.prepareCustomPermissionForSave()) {
                    return;
                }

                const payload = {
                    can_view: this.form.can_view,
                    can_create: this.form.can_create,
                    can_update: this.form.can_update,
                    can_delete: this.form.can_delete,
                    can_export: this.form.can_export,
                    custom_permissions: this.form.custom_permissions.map(permission => ({
                        ...permission
                    }))
                };
                const request = this.isEdit ?
                    axios.put(
                        "{{ route('master-menus.update', ['masterMenu' => '__ID__']) }}".replace('__ID__', this.form.id), {
                            display_name: this.form.display_name,
                            ...payload
                        }
                    ) :
                    axios.post("{{ route('master-menus.store') }}", {
                        name: this.form.name,
                        display_name: this.form.display_name,
                        ...payload
                    });

                request
                    .then(response => {
                        document.getElementById('master-menu-modal').close();
                        window.dispatchEvent(new CustomEvent('master-menu-saved'));
                        window.dispatchEvent(new CustomEvent('show-toast', {
                            detail: {
                                title: 'Berhasil',
                                message: response.data.message || 'Permission module berhasil disimpan.',
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
                                    'Anda tidak memiliki permission untuk menyimpan perubahan module.' : error.response?.data?.message || 'Permission module tidak dapat disimpan.',
                                type: 'error'
                            }
                        }));
                    });
            }
        }));
    });
</script>