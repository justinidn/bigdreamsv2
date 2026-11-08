import 'bootstrap';
import axios from 'axios';
import Alpine from 'alpinejs';
import dataTable from './components/data-table';
import toastManager from './components/toast-manager';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

window.axios.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 422) {
            window.dispatchEvent(new CustomEvent('validation-failed', {
                detail: { errors: error.response.data?.errors ?? {} },
            }));
        }

        return Promise.reject(error);
    }
);

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.Alpine = Alpine;

// Daftarkan sebagai Alpine.data yang valid
Alpine.data('dataTable', dataTable);
Alpine.data('toastManager', toastManager);

// Teruskan pilihan datepicker Bootstrap ke Alpine x-model
document.addEventListener('change.bs.datepicker', (event) => {
    const input = event.target;
    const date = event.dates?.[0] ?? '';

    // Tunggu Bootstrap selesai mengisi input, lalu pakai format YYYY-MM-DD
    setTimeout(() => {
        input.value = date;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }, 0);
});


Alpine.start();