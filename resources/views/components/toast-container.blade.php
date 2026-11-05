<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 99999;" x-data="toastManager()">
    <template x-for="t in toasts" :key="t.id">
        <div class="toast fade show mb-2 shadow" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" :class="t.type === 'error' ? 'bg-danger text-white' : 'bg-success text-white'">
                <strong class="me-auto" x-text="t.title">Notifikasi</strong>
                <small class="text-white-50">Baru saja</small>
                <button type="button" class="btn-close btn-close-white" @click="remove(t.id)" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-body text-body" x-text="t.message"></div>
        </div>
    </template>
</div>