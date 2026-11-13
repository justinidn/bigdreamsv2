<x-app-layout>
    <div class="container-fluid py-3"
        x-data="companyDetailPage"
        data-endpoint="{{ route('companies.show', $company) }}">

        <!-- Header & Profil Saham -->
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <span class="badge theme-primary fs-6 px-3 py-2">{{ $company->company_code }}</span>
                        <div>
                            <h5 class="mb-0 fw-bold">{{ $company->company_name }}</h5>
                            <small class="text-body-secondary">Ringkasan Pergerakan Harga & Aktivitas Volume</small>
                        </div>
                    </div>

                    <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('dashboard') }}"
                        class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-arrow-left me-1"></i>Kembali
                    </a>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <!-- Kolom Kiri (atau Full Width saat Hourly): Tabel Riwayat Harga OHLCV -->
            <div :class="timeframe === 'daily' ? 'col-12 col-lg-8' : 'col-12'">
                <div class="card h-100">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-semibold">Riwayat Harga</h6>
                            <span class="badge text-bg-secondary text-uppercase" x-text="timeframe"></span>
                        </div>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <select id="timeframeSelect"
                                class="form-select form-select-sm"
                                style="width: 120px;"
                                aria-label="Pilih Timeframe"
                                x-model="timeframe"
                                @change="handleTimeframeChange()"
                                :disabled="loadingPrices">
                                <option value="daily">Daily</option>
                                <option value="hourly">Hourly</option>
                            </select>

                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="fetchPrices()"
                                :disabled="loadingPrices"
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
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th class="text-center" x-text="timeframe === 'hourly' ? 'Timestamp' : 'Date'"></th>
                                        <th style="width: 115px;" class="text-end">Open</th>
                                        <th style="width: 115px;" class="text-end">High</th>
                                        <th style="width: 115px;" class="text-end">Low</th>
                                        <th style="width: 115px;" class="text-end">Close</th>
                                        <th style="width: 155px;" class="text-end">Volume</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider"
                                    :style="loadingPrices ? 'opacity: 0.5; transition: opacity 0.2s;' : 'opacity: 1; transition: opacity 0.2s;'">
                                    <template x-for="(row, index) in prices" :key="row.id">
                                        <tr :class="getBreakoutRowTheme(row, index)">
                                            <td class="text-center small align-middle"
                                                :class="getBreakoutRowTheme(row, index) || 'text-body-secondary'"
                                                x-text="index + 1"></td>

                                            <td class="text-center fw-medium align-middle"
                                                :class="getBreakoutRowTheme(row, index)">
                                                <span x-text="row.time_label"></span>
                                                <template x-if="isVolumeBreakout(row)">
                                                    <span class="badge ms-1"
                                                        :class="getBreakoutBadgeTheme(row, index)"
                                                        title="Volume Breakout">VOL</span>
                                                </template>
                                            </td>

                                            <td class="text-end font-monospace align-middle"
                                                :class="getBreakoutRowTheme(row, index)"
                                                x-text="formatNumber(row.open)"></td>

                                            <td class="text-end font-monospace align-middle"
                                                :class="getBreakoutRowTheme(row, index)"
                                                x-text="formatNumber(row.high)"></td>

                                            <td class="text-end font-monospace align-middle"
                                                :class="getBreakoutRowTheme(row, index)"
                                                x-text="formatNumber(row.low)"></td>

                                            <td class="text-end font-monospace fw-bold align-middle"
                                                :class="getBreakoutRowTheme(row, index) || getCloseTextClass(row, index)"
                                                x-text="formatNumber(row.close)"></td>

                                            <td class="text-end font-monospace align-middle"
                                                :class="isVolumeBreakout(row) ? getBreakoutRowTheme(row, index) + ' fw-bold' : 'text-body-secondary'"
                                                x-text="formatNumber(row.volume)"></td>
                                        </tr>
                                    </template>

                                    <template x-if="!loadingPrices && prices.length === 0">
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

                    <div class="card-footer d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <small class="text-body-secondary">
                            Menampilkan <span class="fw-semibold" x-text="prices.length"></span> data terbaru
                        </small>
                        <div class="d-flex align-items-center gap-2" x-show="timeframe === 'daily'">
                            <span class="badge theme-success">VOL Naik</span>
                            <span class="badge theme-danger">VOL Turun</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Kolom Kanan: Widget Volume Breakout (Hanya Tampil saat Timeframe Daily) -->
            <div class="col-12 col-lg-4" x-show="timeframe === 'daily'" x-transition>
                <div class="card h-100">
                    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <h6 class="mb-0 fw-semibold">Volume Breakout</h6>
                            <span class="badge theme-primary rounded-pill" x-text="volumeSpikes.length"></span>
                        </div>

                        <div class="d-flex align-items-center gap-2 ms-auto">
                            <select id="minSurgeSelect"
                                class="form-select form-select-sm"
                                style="width: 115px;"
                                aria-label="Minimal Lonjakan Volume"
                                x-model="minSurge"
                                @change="fetchVolumeSpikes()"
                                :disabled="loadingSpikes">
                                <option value="100">&ge; +100%</option>
                                <option value="150">&ge; +150%</option>
                                <option value="200">&ge; +200%</option>
                            </select>

                            <button type="button"
                                class="btn btn-outline-secondary btn-sm"
                                @click="fetchVolumeSpikes()"
                                :disabled="loadingSpikes"
                                title="Refresh Volume Breakout">
                                <i class="bi bi-arrow-clockwise"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px; overflow-y: auto;">
                            <table class="table table-bordered table-hover table-sm mb-0">
                                <thead class="sticky-top">
                                    <tr class="theme-dark">
                                        <th class="text-center">Date</th>
                                        <th class="text-end">Volume</th>
                                        <th style="width: 105px;" class="text-end">Surge</th>
                                    </tr>
                                </thead>
                                <tbody class="table-group-divider"
                                    :style="loadingSpikes ? 'opacity: 0.5; transition: opacity 0.2s;' : 'opacity: 1; transition: opacity 0.2s;'">
                                    <template x-for="spike in volumeSpikes" :key="spike.id">
                                        <tr>
                                            <td class="text-center fw-medium align-middle" x-text="spike.date"></td>
                                            <td class="text-end font-monospace align-middle" x-text="formatNumber(spike.volume)"></td>
                                            <td class="text-end align-middle">
                                                <span class="badge theme-success font-monospace"
                                                    x-text="formatSurge(spike.surge_percent)"></span>
                                            </td>
                                        </tr>
                                    </template>

                                    <template x-if="!loadingSpikes && volumeSpikes.length === 0">
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-body-secondary">
                                                Belum ada lonjakan volume &ge; +<span x-text="minSurge"></span>%.
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer d-flex justify-content-between align-items-center">
                        <small class="text-body-secondary">Basis: VMA 20 Hari</small>
                        <small class="text-body-secondary" x-show="loadingSpikes">Menghitung...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @once
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('companyDetailPage', () => ({
                endpoint: '',
                timeframe: 'daily',
                prices: [],
                loadingPrices: false,

                minSurge: '100',
                volumeSpikes: [],
                loadingSpikes: false,

                init() {
                    this.endpoint = this.$el.dataset.endpoint || '';

                    const urlParams = new URLSearchParams(window.location.search);
                    const initialTf = urlParams.get('timeframe');
                    if (initialTf === 'daily' || initialTf === 'hourly') {
                        this.timeframe = initialTf;
                    }

                    this.fetchPrices();
                    if (this.timeframe === 'daily') {
                        this.fetchVolumeSpikes();
                    }
                },

                handleTimeframeChange() {
                    this.fetchPrices();
                    if (this.timeframe === 'daily' && this.volumeSpikes.length === 0) {
                        this.fetchVolumeSpikes();
                    }
                },

                fetchPrices() {
                    if (!this.endpoint) return;
                    this.loadingPrices = true;

                    axios.get(this.endpoint, {
                            params: {
                                section: 'prices',
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
                            this.loadingPrices = false;
                        });
                },

                fetchVolumeSpikes() {
                    if (!this.endpoint) return;
                    this.loadingSpikes = true;

                    axios.get(this.endpoint, {
                            params: {
                                section: 'volume_spikes',
                                min_surge: this.minSurge
                            },
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then((response) => {
                            this.volumeSpikes = response.data.data || [];
                        })
                        .catch((error) => {
                            console.error('Gagal mengambil data lonjakan volume:', error);
                            this.volumeSpikes = [];
                        })
                        .finally(() => {
                            this.loadingSpikes = false;
                        });
                },

                isVolumeBreakout(row) {
                    if (this.timeframe !== 'daily' || !row || !row.time_label) {
                        return false;
                    }
                    const rowDate = String(row.time_label).slice(0, 10);
                    return this.volumeSpikes.some((spike) => String(spike.date).slice(0, 10) === rowDate);
                },

                getPriceDirection(row, index) {
                    const currentClose = Number(row.close);
                    if (Number.isNaN(currentClose)) {
                        return 0;
                    }

                    const prevRow = this.prices[index + 1];
                    const referencePrice = prevRow ? Number(prevRow.close) : Number(row.open);

                    if (Number.isNaN(referencePrice)) {
                        return 0;
                    }

                    if (currentClose > referencePrice) return 1;
                    if (currentClose < referencePrice) return -1;
                    return 0;
                },

                getBreakoutRowTheme(row, index) {
                    if (!this.isVolumeBreakout(row)) {
                        return '';
                    }
                    const direction = this.getPriceDirection(row, index);
                    if (direction > 0) return 'theme-success';
                    if (direction < 0) return 'theme-danger';
                    return 'theme-warning';
                },

                getBreakoutBadgeTheme(row, index) {
                    const direction = this.getPriceDirection(row, index);
                    if (direction > 0) return 'theme-success';
                    if (direction < 0) return 'theme-danger';
                    return 'theme-warning';
                },

                getCloseTextClass(row, index) {
                    const direction = this.getPriceDirection(row, index);
                    if (direction > 0) return 'text-success';
                    if (direction < 0) return 'text-danger';
                    return '';
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
                },

                formatSurge(value) {
                    if (value === null || value === undefined || value === '') {
                        return '-';
                    }
                    const num = Number(value);
                    if (Number.isNaN(num)) {
                        return value;
                    }
                    return '+' + num.toLocaleString('id-ID', {
                        minimumFractionDigits: 1,
                        maximumFractionDigits: 1
                    }) + '%';
                }
            }));
        });
    </script>
    @endonce
</x-app-layout>