<x-app-layout>
    <div class="container-fluid py-3"
        x-data="companyPriceDetail"
        data-endpoint="{{ route('companies.show', $company) }}">

        <!-- Header & Profil Saham -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge text-bg-primary fs-6 px-3 py-2">{{ $company->company_code }}</span>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $company->company_name }}</h5>
                            <small class="text-body-secondary">Ringkasan Pergerakan Harga Saham</small>
                        </div>
                    </div>

                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Harga (20 Baris Scrollable, Dropdown di Pojok Kanan) -->
        <div class="card">
            <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h6 class="mb-0 fw-semibold">Riwayat Harga</h6>
                    <span class="badge text-bg-secondary text-uppercase" x-text="timeframe"></span>
                </div>

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <select id="timeframeSelect"
                        class="form-select form-select-sm"
                        style="width: 130px;"
                        aria-label="Pilih Timeframe"
                        x-model="timeframe"
                        @change="fetchPrices()"
                        :disabled="loading">
                        <option value="daily">Daily</option>
                        <option value="hourly">Hourly</option>
                    </select>

                    <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        @click="fetchPrices()"
                        :disabled="loading"
                        title="Refresh Harga">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                    <table class="table table-bordered table-hover table-sm mb-0">
                        <thead class="sticky-top">
                            <tr class="theme-dark">
                                <th style="width: 50px;" class="text-center">#</th>
                                <th class="text-center" x-text="timeframe === 'hourly' ? 'Timestamp' : 'Date'"></th>
                                <th style="width: 135px;" class="text-end">Open</th>
                                <th style="width: 135px;" class="text-end">High</th>
                                <th style="width: 135px;" class="text-end">Low</th>
                                <th style="width: 135px;" class="text-end">Close</th>
                                <th style="width: 160px;" class="text-end">Volume</th>
                            </tr>
                        </thead>
                        <tbody class="table-group-divider"
                            :style="loading ? 'opacity: 0.5; transition: opacity 0.2s;' : 'opacity: 1; transition: opacity 0.2s;'">
                            <template x-for="(row, index) in prices" :key="row.id">
                                <tr>
                                    <td class="text-center text-body-secondary small align-middle" x-text="index + 1"></td>
                                    <td class="text-center fw-medium align-middle" x-text="row.time_label"></td>
                                    <td class="text-end font-monospace align-middle" x-text="formatNumber(row.open)"></td>
                                    <td class="text-end font-monospace align-middle" x-text="formatNumber(row.high)"></td>
                                    <td class="text-end font-monospace align-middle" x-text="formatNumber(row.low)"></td>
                                    <td class="text-end font-monospace fw-semibold align-middle" x-text="formatNumber(row.close)"></td>
                                    <td class="text-end font-monospace text-body-secondary align-middle" x-text="formatNumber(row.volume)"></td>
                                </tr>
                            </template>

                            <template x-if="!loading && prices.length === 0">
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-body-secondary">
                                        Data harga <span x-text="timeframe"></span> tidak ditemukan untuk emiten ini.
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card-footer d-flex justify-content-between align-items-center">
                <small class="text-body-secondary">
                    Menampilkan <span class="fw-semibold" x-text="prices.length"></span> data terbaru
                </small>
                <small class="text-body-secondary" x-show="loading">Memuat data...</small>
            </div>
        </div>
    </div>

    @once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('companyPriceDetail', () => ({
                endpoint: '',
                timeframe: 'daily',
                prices: [],
                loading: false,

                init() {
                    this.endpoint = this.$el.dataset.endpoint || '';

                    const urlParams = new URLSearchParams(window.location.search);
                    const initialTf = urlParams.get('timeframe');
                    if (initialTf === 'daily' || initialTf === 'hourly') {
                        this.timeframe = initialTf;
                    }

                    this.fetchPrices();
                },

                fetchPrices() {
                    if (!this.endpoint) return;
                    this.loading = true;

                    axios.get(this.endpoint, {
                            params: {
                                timeframe: this.timeframe
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then((response) => {
                            this.prices = response.data.data || [];
                        })
                        .catch((error) => {
                            console.error('Gagal mengambil riwayat harga:', error);
                            this.prices = [];
                        })
                        .finally(() => {
                            this.loading = false;
                        });
                },

                formatNumber(value) {
                    if (value === null || value === undefined || value === '') {
                        return '-';
                    }
                    const num = Number(value);
                    if (Number.isNaN(num)) {
                        return value;
                    }
                    return num.toLocaleString('id-ID');
                }
            }));
        });
    </script>
    @endonce
</x-app-layout>