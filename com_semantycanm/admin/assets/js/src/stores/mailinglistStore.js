import {defineStore} from 'pinia';

export const useMailingListStore = defineStore('mailingList', {
    state: () => ({
        docsListPage: {
            pageSize: 10,
            itemCount: 0,
            pageCount: 1,
            page: 1,
            docs: []
        }
    }),
    actions: {
        async fetchMailingList(currentPage, size, msgPopup, loadingBar) {
            loadingBar.start()

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
                        this.docsListPage.pageSize = size;
                        this.docsListPage.itemCount = respData.data.count;
                        this.docsListPage.pageCount = respData.data.maxPage;
                        this.docsListPage.page = respData.data.current;
                    }
                })
                .catch(error => {
                    loadingBar.error()
                    msgPopup.error(error.message);
                })
                .finally(() => {
                    loadingBar.finish()
                });
        },
        async fetchEntryDetails(id, msgPopup, loadingBar, detailed = false) {
            loadingBar.start()

            const url = `index.php?option=com_semantycanm&task=MailingList.find&id=${encodeURIComponent(id)}&detailed=${detailed}`;

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
                loadingBar.error()
                msgPopup.error(error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
                throw error;
            } finally {
                loadingBar.finish()
            }
        },
        async deleteMailingListEntries(ids, msgPopup, loadingBar) {
            loadingBar.start()
            const idsParam = ids.join(',');
            const url = 'index.php?option=com_semantycanm&task=MailingList.delete&ids=' + encodeURIComponent(idsParam);

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
                loadingBar.error()
                msgPopup.error(error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
            } finally {
                loadingBar.finish()
            }
        }
    }
});
