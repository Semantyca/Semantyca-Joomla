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
        },
        async fetchEntryDetails(id, msgPopup) {
            startLoading('loadingSpinner');
            const url = `index.php?option=com_yourcomponent&task=find&id=${encodeURIComponent(id)}`;

            try {
                const response = await fetch(url, {
                    method: 'GET',
                });
                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }
                const respData = await response.json();
                if (respData.success) {
                    return respData.data;
                } else {
                    throw new Error('Error from server: ' + (respData.message || 'Unknown error'));
                }
            } catch (error) {
                msgPopup.error(error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
                throw error;
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async deleteMailingListEntries(ids, msgPopup) {
            startLoading('loadingSpinner');
            const idsParam = ids.join(',');
            const url = 'index.php?option=com_semantycanm&task=delete&ids=' + encodeURIComponent(idsParam);

            try {
                const response = await fetch(url, {
                    method: 'DELETE',
                });

                if (!response.ok) {
                    throw new Error('Network response was not ok: ' + response.statusText);
                }

                const respData = await response.json();

                if (respData.success) {
                    msgPopup.info('The mailing list deleted');
                } else {
                    throw new Error('Error from server: ' + (respData.message || 'Unknown error'));
                }
            } catch (error) {
                msgPopup.error(error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
