@props([
'title' => 'Screener Watchlist',
'height' => '540px',
])

@can('screeners.view')
<div x-data="screenerDashboardWidget"
    data-detail-url="{{ route('companies.index') }}"
    data-static-card-widget
    style="--widget-card-height: {{ $height }};">
    <x-table-data name="dashboardScreenersList" endpoint="{{ route('screeners.index', ['per_page' => 500]) }}" :pagination="false">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">{{ $title }}</h6>
                        <span class="badge text-bg-primary rounded-pill" x-text="items.total || 0"></span>
                    </div>
                    <small class="text-body-secondary">
                        <template x-if="items.data && items.data.length > 0">
                            <span>Tanggal: <span class="fw-medium" x-text="items.data[0].date"></span></span>
                        </template>
                        <template x-if="!items.data || items.data.length === 0">
                            <span>Tidak ada data terbaru</span>
                        </template>
                    </small>
                </div>

                <div class="btn-group btn-group-sm" role="group">
                    <button type="button"
                        class="btn btn-outline-secondary"
                        @click="refreshWidget()"
                        :disabled="loading"
                        title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
                    <a href="{{ route('screeners.index') }}"
                        class="btn btn-outline-primary"
                        title="Lihat Semua Screener">
                        Lihat Semua
                    </a>
                </div>
            </div>
        </x-slot>

        <div class="w-100">
            <label class="form-control form-control-sm form-adorn mb-0">
                <div class="form-adorn-icon" aria-hidden="true">
                    <i class="bi bi-search"></i>
                </div>
                <input type="search"
                    class="form-ghost w-100"
                    placeholder="Cari kode atau nama emiten..."
                    x-model.trim="filters.search"
                    @input.debounce.350ms="applyFilters()">
            </label>
        </div>

        <x-slot name="thead">
            <th style="width: 45px;" class="text-center">#</th>
            <th>Emiten</th>
            <th style="width: 100px;" class="text-center">Tanggal</th>
            <th style="width: 95px;" class="text-end">Close</th>
            <th style="width: 120px;" class="text-end">Volume</th>
            <th style="width: 45px;" class="text-center"></th>
        </x-slot>

        <x-slot name="tbody">
            <td class="text-center text-body-secondary small align-middle"
                role="button"
                @click="goToDetail(item.company_id)"
                x-text="index + 1"></td>

            <td class="align-middle"
                role="button"
                @click="goToDetail(item.company_id)">
                <div class="fw-bold" x-text="item.company?.company_code ?? '-'"></div>
                <small class="text-body-secondary d-block text-truncate"
                    style="max-width: 160px;"
                    x-text="item.company?.company_name ?? ''"
                    :title="item.company?.company_name ?? ''"></small>
            </td>

            <td class="text-center text-body-secondary small align-middle"
                role="button"
                @click="goToDetail(item.company_id)"
                x-text="item.date"></td>

            <td class="text-end font-monospace fw-semibold align-middle"
                role="button"
                @click="goToDetail(item.company_id)"
                x-text="formatNumber(item.close_price)"></td>

            <td class="text-end font-monospace text-body-secondary align-middle"
                role="button"
                @click="goToDetail(item.company_id)"
                x-text="formatNumber(item.daily_volume)"></td>

            <td class="text-center align-middle">
                @can('companies.view')
                <template x-if="item.company_id">
                    <a :href="buildDetailUrl(item.company_id)"
                        class="btn btn-sm btn-link text-body-secondary p-0"
                        title="Detail Harga Daily & Hourly">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </template>
                @endcan
            </td>
        </x-slot>
    </x-table-data>
</div>

@once('static-card-widget-style')
<style>
    [data-static-card-widget] .card {
        height: var(--widget-card-height, 540px);
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    [data-static-card-widget] .card-header,
    [data-static-card-widget] .card-footer {
        flex-shrink: 0;
    }

    [data-static-card-widget] .card-body {
        flex: 1 1 auto;
        min-height: 0;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    [data-static-card-widget] .table-responsive {
        flex: 1 1 auto;
        min-height: 0;
        overflow-y: auto;
    }

    [data-static-card-widget] .table-responsive thead {
        position: sticky;
        top: 0;
        z-index: 2;
    }
</style>
@endonce

@once
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('screenerDashboardWidget', () => ({
            detailBaseUrl: '',

            init() {
                this.detailBaseUrl = (this.$el.dataset.detailUrl || '').replace(/\/+$/, '');
            },

            refreshWidget() {
                const table = this.$el.querySelector('.card');
                if (table && Alpine.$data(table)) {
                    Alpine.$data(table).fetchData(1);
                }
            },

            buildDetailUrl(companyId) {
                if (!companyId || !this.detailBaseUrl) return '#';
                return this.detailBaseUrl + '/' + encodeURIComponent(companyId);
            },

            goToDetail(companyId) {
                if (!companyId || !this.detailBaseUrl) return;
                window.location.href = this.buildDetailUrl(companyId);
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
@endcan