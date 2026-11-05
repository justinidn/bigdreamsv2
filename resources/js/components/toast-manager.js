export default () => ({
    toasts: [],
    init() {
        // Daftarkan listener global langsung di window
        window.addEventListener('show-toast', event => {
            this.show(event.detail);
        });
    },
    show(detail) {
        const id = Date.now();
        this.toasts.push({
            id: id,
            title: detail.title || 'Informasi',
            message: detail.message || '',
            type: detail.type || 'success'
        });

        setTimeout(() => {
            this.remove(id);
        }, 4000);
    },
    remove(id) {
        this.toasts = this.toasts.filter(t => t.id !== id);
    }
});