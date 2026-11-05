export default function dataTable(endpoint, showPagination) {
    return {
        items: {
            data: [],
            from: 1,
            to: 0,
            total: 0,
            current_page: 1,
            last_page: 1,
            prev_page_url: null,
            next_page_url: null
        },
        targetPage: 1,
        filters: {},
        showPagination: showPagination,
        endpoint: endpoint,
        columnCount: 1,
        loading: false,

        init() {
            this.fetchData(1);
            this.$nextTick(() => {
                const thElements = this.$el.querySelectorAll('thead th');
                if (thElements.length > 0) {
                    this.columnCount = thElements.length;
                }
            });
        },

        get visiblePages() {
            const total = this.items.last_page || 1;
            const current = this.items.current_page || 1;
            const maxVisible = 4;

            let start = Math.max(1, current - Math.floor(maxVisible / 2));
            let end = start + maxVisible - 1;

            if (end > total) {
                end = total;
                start = Math.max(1, end - maxVisible + 1);
            }

            const pages = [];
            for (let i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        },

        fetchData(page = 1) {
            this.loading = true;

            axios.get(this.endpoint, {
                params: Object.assign({ page: page }, this.filters),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => {
                const resData = response.data;
                this.items = resData.data && !resData.current_page ? resData.data : resData;
                this.targetPage = this.items.current_page || 1;
            })
            .catch(error => {
                console.error("Gagal mengambil data:", error);
            })
            .finally(() => {
                this.loading = false;
            });
        },

        applyFilters() {
            this.fetchData(1);
        },

        changePage(page) {
            if (page >= 1 && page <= this.items.last_page && !this.loading) {
                this.fetchData(page);
            }
        },

        goToPage() {
            if (this.targetPage >= 1 && this.targetPage <= this.items.last_page) {
                this.changePage(this.targetPage);
            } else {
                this.targetPage = this.items.current_page;
            }
        }
    };
}