import { ref } from 'vue';

class PaginatedData {
    constructor() {
        this.page = ref(1);
        this.pageSize = ref(10);
        this.itemCount = ref(0);
        this.pageCount = ref(1);
        this.pages = ref(new Map());
    }

    updateData(data) {
        const { docs, count, maxPage, current } = data;
        this.page.value = current;
        this.itemCount.value = count;
        this.pageCount.value = maxPage;
        this.pages.value.set(current, { docs });
    }

    getCurrentPageData() {
        const pageData = this.pages.value.get(this.page.value);
        return pageData ? pageData.docs : [];
    }

    getAllDocs() {
        const allDocs = [];
        for (const page of this.pages.value.values()) {
            allDocs.push(...page.docs);
        }
        return allDocs;
    }

    setPage(newPage) {
        if (newPage >= 1 && newPage <= this.pageCount.value) {
            this.page.value = newPage;
        }
    }

    setPageSize(newSize) {
        this.pageSize.value = newSize;
    }

    hasNextPage() {
        return this.page.value < this.pageCount.value;
    }

    hasPreviousPage() {
        return this.page.value > 1;
    }

    nextPage() {
        if (this.hasNextPage()) {
            this.setPage(this.page.value + 1);
        }
    }

    previousPage() {
        if (this.hasPreviousPage()) {
            this.setPage(this.page.value - 1);
        }
    }
}

export default PaginatedData;