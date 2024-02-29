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
        async fetchStatisticsData(page, size, pagination) {
            try {
                startLoading('loadingSpinner');
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
                showAlertBar("Error: " + error);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async fetchEvents(eventId) {
            try {
                startLoading('loadingSpinner');
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.getEvents&eventid=' + eventId);
                const respData = await response.json();
                if (respData.success && respData.data) {
                    console.log('resp', respData.data)
                    this.eventListPage.docs[eventId] = respData.data.docs;
                }
            } catch (error) {
                showAlertBar("Error: " + error);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
