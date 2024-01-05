class Pagination {
    constructor(idPart, updateFunc) {
        this.idPart = idPart;
        this.updatePageFunction = updateFunc;

        this.setupEventListeners();
    }

    _constructId(baseId) {
        return `${baseId}${this.idPart}`;
    }

    setupEventListeners() {
        const firstPageBtn = document.getElementById(this._constructId('firstPage'));
        const previousPageBtn = document.getElementById(this._constructId('previousPage'));
        const nextPageBtn = document.getElementById(this._constructId('nextPage'));
        const lastPageBtn = document.getElementById(this._constructId('lastPage'));

        if (firstPageBtn) {
            firstPageBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.goToFirstPage();
            });
        }

        if (previousPageBtn) {
            previousPageBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.goToPreviousPage();
            });
        }

        if (nextPageBtn) {
            nextPageBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.goToNextPage();
            });
        }

        if (lastPageBtn) {
            lastPageBtn.addEventListener('click', (event) => {
                event.preventDefault();
                this.goToLastPage();
            });
        }
    }

    getTotalPages() {
        const totalPageInputId = this._constructId('max');
        const totalPageElement = document.getElementById(totalPageInputId);
        return totalPageElement ? parseInt(totalPageElement.value) : 1;
    }

    getCurrentPage() {
        const currentPageInputId = this._constructId('current');
        const currentPageElement = document.getElementById(currentPageInputId);
        return currentPageElement ? parseInt(currentPageElement.value) : 1;
    }

    goToFirstPage() {
        this.updatePageFunction(1);
    }

    goToPreviousPage() {
        const currentPage = this.getCurrentPage();
        if (currentPage > 1) {
            this.updatePageFunction(currentPage - 1);
        }
    }

    goToNextPage() {
        const currentPage = this.getCurrentPage();
        const totalPages = this.getTotalPages();
        if (currentPage < totalPages) {
            this.updatePageFunction(currentPage + 1);
        }
    }

    goToLastPage() {
        const totalPages = this.getTotalPages();
        this.updatePageFunction(totalPages);
    }
}

window.Pagination = Pagination;
