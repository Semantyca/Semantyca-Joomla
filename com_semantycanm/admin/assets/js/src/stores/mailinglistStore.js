import {defineStore} from 'pinia';

export const useMailingListStore = defineStore('mailingList', {
    state: () => ({
        docsListPage: {
            docs: []
        }
    }),
    actions: {
        async fetchMailingList(currentPage, size, pagination) {
            startLoading('loadingSpinner');

            const url = 'index.php?option=com_semantycanm&task=MailingList.findall&page=' + currentPage + '&limit=' + size;

            fetch(url, {
                method: 'GET',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(respData => {
                    if (respData.success && respData.data) {
                        this.docsListPage.docs = respData.data.docs;
                        if (pagination) {
                            pagination.pageSize = size;
                            pagination.itemCount = respData.data.count;
                            pagination.pageCount = respData.data.maxPage;
                            pagination.page = respData.data.current;
                        }
                    }
                })
                .catch(error => {
                    showErrorBar('MailingList.findall', error.message);
                })
                .finally(() => {
                    stopLoading('loadingSpinner');
                });
        }
    }
});
