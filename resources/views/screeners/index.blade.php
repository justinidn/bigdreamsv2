<x-app-layout>
    <div x-data="screenerPage" x-on:screener-saved.window="refreshTable()">
        <x-table-data name="screenersList" endpoint="{{ route('screeners.index') }}" :pagination="true">

            <div class="d-flex align-items-center gap-2 w-100">
                <label class="form-control form-control-sm form-adorn flex-grow-1 mb-0">
                    <div class="form-adorn-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="search" class="form-ghost w-100" placeholder="Search Screener" x-model.trim="filters.search" @input.debounce.400ms="applyFilters()">
                </label>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="d-inline-block rounded-3" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Advanced Search">
                        <button class="btn-solid theme-primary btn-sm" type="button" data-bs-toggle="drawer" data-bs-target="#drawerRight" aria-controls="drawerRight"><i class="bi bi-funnel-fill"></i></button>
                    </span>
                    @can('screeners.create')
                    <x-button-add @click="$dispatch('open-screeners-modal')" data-bs-toggle="dialog" data-bs-target="#screenersModal" data-bs-backdrop="static" />
                    @endcan
                </div>
            </div>

            <dialog class="drawer drawer-end" data-bs-backdrop="false" data-bs-scroll="true" tabindex="-1" id="drawerRight" aria-labelledby="drawerRightLabel">
                <div class="drawer-header">
                    <h5 class="drawer-title" id="drawerRightLabel">Advanced Filters</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="drawer" aria-label="Close"></button>
                </div>
                <div class="drawer-body">
                    <div class="mb-1">
                        <label for="start_date" class="form-label">Start Date</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon1"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" class="form-control form-control-sm" id="start_date" data-bs-toggle="datepicker" data-bs-placement="auto" autocomplete="off" placeholder="YYYY-MM-DD" x-model="filters.start_date">
                        </div>
                    </div>
                    <div class="mb-1">
                        <label for="end_date" class="form-label">End Date</label>
                        <div class="input-group">
                            <span class="input-group-text" id="basic-addon2"><i class="bi bi-calendar-week"></i></span>
                            <input type="text" class="form-control form-control-sm" id="end_date" data-bs-toggle="datepicker" data-bs-placement="auto" autocomplete="off" placeholder="YYYY-MM-DD" x-model="filters.end_date">
                        </div>
                    </div>

                    <x-button-search @click="applyFilters()" />
                </div>
            </dialog>

            <x-slot name="thead" class="text-center">
                <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Company Code</th>
                <th style="width: 140px;" class="text-center">Date</th>
                <th style="width: 140px;" class="text-center">Current Price</th>
                <th style="width: 140px;" class="text-center">Next Date</th>
                <th style="width: 140px;" class="text-center">Closing Price</th>
                <th style="width: 140px;" class="text-center">Result</th>

            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.company?.company_code"></td>
                <td class="text-center" x-text="item.date"></td>
                <td class="text-center" x-text="item.close_price"></td>
                <td class="text-center" x-text="item.next_date"></td>
                <td class="text-center" x-text="item.next_high_price"></td>
                <td class="text-center" x-text="item.is_profitable"></td>
            </x-slot>
        </x-table-data>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('screenerPage', () => ({
                refreshTable() {
                    const table = this.$el.querySelector('.card');
                    if (table && Alpine.$data(table)) {
                        Alpine.$data(table).fetchData(1);
                    }
                }
            }));
        });
    </script>
</x-app-layout>