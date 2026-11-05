@props([
'name' => 'dataTable',
'endpoint' => '',
'pagination' => true,
])

<div class="card"
    x-data="dataTable('{{ $endpoint }}', {{ filter_var($pagination, FILTER_VALIDATE_BOOLEAN) ? 'true' : 'false' }})"
    @department-saved.window="fetchData(items.current_page)">

    <div class="card-header">
        {{ $header ?? 'Data List' }}
    </div>

    <div class="card-body d-flex flex-column" style="min-height: 500px;">
        <form class="row lg:row-cols-auto g-3 align-items-center mb-3" @submit.prevent="applyFilters">
            {{ $slot }}
        </form>

        <div class="table-responsive">
            <table class="table table-bordered table-responsive table-card table-hover table-sm">
                <thead>
                    <tr class="theme-dark">
                        {{ $thead }}
                    </tr>
                </thead>
                <tbody class="table-group-divider" :style="loading ? 'opacity: 0.5; transition: opacity 0.2s;' : 'opacity: 1; transition: opacity 0.2s;'">
                    <template x-for="(item, index) in items.data" :key="item.id">
                        <tr>
                            {{ $tbody }}
                        </tr>
                    </template>
                    <template x-if="!loading && items.data && items.data.length === 0">
                        <tr>
                            <td :colspan="columnCount" class="text-center py-4 text-muted">Data tidak ditemukan.</td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <!-- Bagian Pagination -->
        <div class="d-flex flex-wrap justify-content-between align-items-center mt-auto pt-3 border-top gap-2" x-show="showPagination && items.total > 0">
            <small class="text-muted">
                Menampilkan <span x-text="items.from"></span> sampai <span x-text="items.to"></span> dari <span x-text="items.total"></span> entri
            </small>

            <div class="d-flex align-items-center gap-2">
                <div class="d-flex align-items-center gap-1 me-2">
                    <small class="text-muted">Ke hal:</small>
                    <input type="number" class="form-control form-control-sm text-center" style="width: 60px;" x-model.number="targetPage" @keydown.enter="goToPage()" min="1" :max="items.last_page">
                </div>

                <nav aria-label="Page navigation">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item" :class="{ 'disabled': items.current_page === 1 }">
                            <button type="button" class="page-link" @click="changePage(1)" :disabled="items.current_page === 1">&laquo;</button>
                        </li>
                        <li class="page-item" :class="{ 'disabled': !items.prev_page_url }">
                            <button type="button" class="page-link" @click="changePage(items.current_page - 1)" :disabled="!items.prev_page_url">&lsaquo;</button>
                        </li>

                        <template x-for="page in visiblePages" :key="page">
                            <li class="page-item" :class="{ 'active': page === items.current_page }">
                                <button type="button" class="page-link" @click="changePage(page)" x-text="page"></button>
                            </li>
                        </template>

                        <li class="page-item" :class="{ 'disabled': !items.next_page_url }">
                            <button type="button" class="page-link" @click="changePage(items.current_page + 1)" :disabled="!items.next_page_url">&rsaquo;</button>
                        </li>
                        <li class="page-item" :class="{ 'disabled': items.current_page === items.last_page }">
                            <button type="button" class="page-link" @click="changePage(items.last_page)" :disabled="items.current_page === items.last_page">&raquo;</button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</div>