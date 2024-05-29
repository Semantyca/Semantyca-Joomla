import {defineStore} from 'pinia';

export const useStatStore = defineStore('stat', {
    state: () => ({
        docsListPage: {
            docs: []
        },
        eventListPage: {
            docs: {}
        }
    }),
    actions: {
        async fetchStatisticsData(page, size, pagination, msgPopup, loadingBar) {
            try {
                loadingBar.start()
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.findAll&page=' + page + '&limit=' + size);
                const respData = await response.json();
                if (respData.success && respData.data) {
                    this.docsListPage.docs = respData.data.docs;
                    pagination.pageSize = size;
                    pagination.itemCount = respData.data.count;
                    pagination.pageCount = respData.data.maxPage;
                    pagination.page = respData.data.current;
                }
            } catch (error) {
                loadingBar.error()
                msgPopup.error(error.message);
            } finally {
                loadingBar.finish()
            }
        },
        async fetchEvents(eventId, msgPopup, loadingBar) {
            try {
                loadingBar.start()
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.getEvents&eventid=' + eventId);
                const respData = await response.json();
                if (respData.success && respData.data) {
                    console.log('resp', respData.data)
                    this.eventListPage.docs[eventId] = respData.data.docs;
                }
            } catch (error) {
                loadingBar.error()
                msgPopup.error(error.message);
            } finally {
                loadingBar.finish()
            }
        }
    }
});
