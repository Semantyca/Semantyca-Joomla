import {defineStore} from 'pinia';

export const useMailingListStore = defineStore('mailingList', {
    state: () => ({
        mailingListDocsView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        },
        mailingList: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        }
    }),
    actions: {
        async refreshMailingList(currentPage) {
            startLoading('loadingSpinner');

            const url = 'index.php?option=com_semantycanm&task=MailingList.findall&page=' + currentPage + '&limit=10';

            fetch(url, {
                method: 'GET',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        this.mailingListDocsView.total = data.data.count;
                        this.mailingListDocsView.current = data.data.current;
                        this.mailingListDocsView.maxPage = data.data.maxPage;
                        this.mailingListDocsView.docs = data.data.docs;
                    }
                })
                .catch(error => {
                    showErrorBar('MailingList.findall', error.message);
                })
                .finally(() => {
                    stopLoading('loadingSpinner');
                });
        },
        async getPageOfMailingList(page) {
            try {
                startLoading('loadingSpinner');
                const response = await fetch('index.php?option=com_semantycanm&task=MailingList.findall&page=' + page + '&limit=10');
                const viewData = await response.json();
                if (viewData.success && viewData.data) {
                    this.mailingList.totalStatList = viewData.data.count;
                    this.mailingList.currentStatList = viewData.data.current;
                    this.mailingList.maxStatList = viewData.data.maxPage;
                    this.mailingList.docs = viewData.data.docs;
                }
            } catch (error) {
                showErrorBar('MailingList.findall', error);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
