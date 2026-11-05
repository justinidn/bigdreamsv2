import 'bootstrap';
import axios from 'axios';
import Alpine from 'alpinejs';
import dataTable from './components/data-table';
import toastManager from './components/toast-manager';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

let token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.Alpine = Alpine;

// Daftarkan sebagai Alpine.data yang valid
Alpine.data('dataTable', dataTable);
Alpine.data('toastManager', toastManager);

Alpine.start();