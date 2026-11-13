@props([
'title' => 'Volume Breakout',
'height' => '540px',
])

@canany(['companies.view', 'transaction-dailies.view'])
<div data-company-detail-base="{{ route('companies.index') }}"
    data-static-card-widget
    style="--widget-card-height: {{ $height }};">
    <x-table-data name="dashboardVolumeBreakoutList" endpoint="{{ route('companies.volume-breakouts') }}" :pagination="false">
        <x-slot name="header">
            <div class="d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h6 class="mb-0 fw-semibold">{{ $title }}</h6>
                        <span class="badge theme-primary rounded-pill" x-text="items.total || (items.data ? items.data.length : 0)"></span>
                    </div>
                    <small class="text-body-secondary">
                        <template x-if="items.data && items.data.length > 0">
                            <span>Tanggal: <span class="fw-medium" x-text="items.data[0].date"></span> (VMA 20 Hari)</span>
                        </template>
                        <template x-if="!items.data || items.data.length === 0">
                            <span>Tidak ada lonjakan volume pada tanggal terakhir</span>
                        </template>
                    </small>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <select class="form-select form-select-sm"
                        style="width: 115px;"
                        aria-label="Minimal Lonjakan Volume"
                        x-model="filters.min_surge"
                        @change="applyFilters()"
                        :disabled="loading">
                        <option value="">&ge; +100%</option>
                        <option value="150">&ge; +150%</option>
                        <option value="200">&ge; +200%</option>
                    </select>

                    <button type="button"
                        class="btn btn-outline-secondary btn-sm"
                        @click="fetchData(1)"
                        :disabled="loading"
                        title="Refresh">
                        <i class="bi bi-arrow-clockwise"></i>
                    </button>
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
            <th style="width: 95px;" class="text-end">Close</th>
            <th style="width: 120px;" class="text-end">Volume</th>
            <th style="width: 105px;" class="text-end">Surge</th>
            <th style="width: 40px;" class="text-center"></th>
        </x-slot>

        <x-slot name="tbody">
            <td class="text-center text-body-secondary small align-middle"
                role="button"
                @click="window.openCompanyDetail($el, item.company_id)"
                x-text="index + 1"></td>

            <td class="align-middle"
                role="button"
                @click="window.openCompanyDetail($el, item.company_id)">
                <div class="fw-bold" x-text="item.company_code ?? '-'"></div>
                <small class="text-body-secondary d-block text-truncate"
                    style="max-width: 180px;"
                    x-text="item.company_name ?? ''"
                    :title="item.company_name ?? ''"></small>
            </td>

            <td class="text-end font-monospace fw-semibold align-middle"
                :class="item.price_direction > 0 ? 'text-success' : (item.price_direction < 0 ? 'text-danger' : '')"
                role="button"
                @click="window.openCompanyDetail($el, item.company_id)"
                x-text="window.formatIdNumber(item.close_price)"></td>

            <td class="text-end font-monospace text-body-secondary align-middle"
                role="button"
                @click="window.openCompanyDetail($el, item.company_id)"
                x-text="window.formatIdNumber(item.volume)"></td>

            <td class="text-end align-middle"
                role="button"
                @click="window.openCompanyDetail($el, item.company_id)">
                <span class="badge font-monospace"
                    :class="item.price_direction > 0 ? 'theme-success' : (item.price_direction < 0 ? 'theme-danger' : 'theme-warning')"
                    x-text="window.formatVolumeSurge(item.surge_percent)"></span>
            </td>

            <td class="text-center align-middle">
                <template x-if="item.company_id">
                    <a :href="window.buildCompanyDetailUrl($el, item.company_id)"
                        class="btn btn-sm btn-link text-body-secondary p-0"
                        title="Detail Harga & Volume Breakout">
                        <i class="bi bi-chevron-right"></i>
                    </a>
                </template>
            </td>
        </x-slot>
    </x-table-data>
</div>

@once
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

<script>
    window.buildCompanyDetailUrl = function(el, companyId) {
        if (!companyId) return '#';
        const wrapper = el ? el.closest('[data-company-detail-base]') : document.querySelector('[data-company-detail-base]');
        const base = wrapper ? (wrapper.dataset.companyDetailBase || '').replace(/\/+$/, '') : '';
        return base ? `${base}/${encodeURIComponent(companyId)}` : '#';
    };

    window.openCompanyDetail = function(el, companyId) {
        const url = window.buildCompanyDetailUrl(el, companyId);
        if (url && url !== '#') {
            window.location.href = url;
        }
    };

    window.formatIdNumber = function(value) {
        if (value === null || value === undefined || value === '') {
            return '-';
        }
        const num = Number(value);
        if (Number.isNaN(num)) {
            return value;
        }
        return num.toLocaleString('id-ID');
    };

    window.formatVolumeSurge = function(value) {
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
    };
</script>
@endonce
@endcanany