<x-app-layout>
    <div x-data="transactionHourlyPage" x-on:transactionHourly-saved.window="refreshTable()">
        <x-table-data name="transactionHourliesList" endpoint="{{ route('transaction-hourlies.index') }}" :pagination="true">

            <div class="d-flex align-items-center gap-2 w-100">
                <label class="form-control form-control-sm form-adorn flex-grow-1 mb-0">
                    <div class="form-adorn-icon" aria-hidden="true">
                        <i class="bi bi-search"></i>
                    </div>
                    <input type="search" class="form-ghost w-100" placeholder="Search TransactionHourly" x-model.trim="filters.search" @input.debounce.400ms="applyFilters()">
                </label>

                <div class="d-flex align-items-center gap-2 flex-shrink-0">
                    <span class="d-inline-block rounded-3" tabindex="0" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-content="Advanced Search">
                        <button class="btn-solid theme-primary btn-sm" type="button" data-bs-toggle="drawer" data-bs-target="#drawerRight" aria-controls="drawerRight"><i class="bi bi-funnel-fill"></i></button>
                    </span>
                
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
                        <input type="text" id="start_date" class="form-control form-control-sm" x-model="filters.start_date" placeholder="Start Date">
                    </div>
                    <div class="mb-1">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="text" id="end_date" class="form-control form-control-sm" x-model="filters.end_date" placeholder="End Date">
                    </div>

                    <x-button-search @click="applyFilters()" />
                </div>
            </dialog>

            <x-slot name="thead" class="text-center">
              <th style="width: 50px;" class="text-center">No</th>
                <th class="text-center">Company Code</th>
                <th style="width: 240px;" class="text-center">Date</th>
                <th style="width: 140px;" class="text-center">Open</th>
                <th style="width: 140px;" class="text-center">High</th>
                <th style="width: 140px;" class="text-center">Low</th>
                <th style="width: 140px;" class="text-center">Close</th>
                <th style="width: 140px;" class="text-center">Volume</th>
            </x-slot>

            <x-slot name="tbody">
                <td class="text-center" x-text="(items.from || 1) + index"></td>
                <td x-text="item.company?.company_code"></td>
                <td class="text-center" x-text="item.timestamp"></td>
                <td class="text-center" x-text="item.open"></td>
                <td class="text-center" x-text="item.high"></td>
                <td class="text-center" x-text="item.low"></td>
                <td class="text-center" x-text="item.close"></td>
                <td class="text-center" x-text="item.volume"></td>
            </x-slot>
        </x-table-data>
<!-- 
        {{-- Include Modal Form --}}
        @include('transaction-hourlies.partials.transaction-hourlies-form') -->
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('transactionHourlyPage', () => ({
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