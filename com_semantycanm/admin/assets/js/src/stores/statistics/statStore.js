import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import StatApiManager from "./StatApiManager";
import NewsletterApiManager from "../newsletter/NewsletterApiManager";

export const useStatStore = defineStore('stat', () => {
    const newsletterListPage = ref({
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        page: 1,
        pages: new Map(),
    });

    const statListPage = ref({
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        page: 1,
        pages: new Map(),
    });

    const eventListPage = ref({
        docs: {}
    });

    const getPagination = computed(() => ({
        page: newsletterListPage.value.page,
        pageSize: newsletterListPage.value.pageSize,
        itemCount: newsletterListPage.value.itemCount,
        pageCount: newsletterListPage.value.pageCount,
        size: 'large',
        showSizePicker: true,
        pageSizes: [10, 20, 50]
    }));

    const getCurrentPage = computed(() => {
        const pageData = newsletterListPage.value.pages.get(newsletterListPage.value.page);
        return pageData ? pageData.docs : [];
    });

    const fetchNewsletterData = async (page, size, msgPopup, loadingBar) => {
        try {
            loadingBar.start();
            const manager = new NewsletterApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(page, size);
            if (respData.success && respData.data) {
                const { docs, count, maxPage, current } = respData.data;
                newsletterListPage.value.page = current;
                newsletterListPage.value.pageSize = size;
                newsletterListPage.value.itemCount = count;
                newsletterListPage.value.pageCount = maxPage;
                newsletterListPage.value.pages.set(page, { docs });
            }
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message);
        } finally {
            loadingBar.finish();
        }
    };

    const fetchStatisticsData = async (page, size, msgPopup, loadingBar) => {
        try {
            loadingBar.start();
            const manager = new StatApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(page, size);
            if (respData.success && respData.data) {
                const { docs, count, maxPage, current } = respData.data;
                statListPage.value.page = current;
                statListPage.value.pageSize = size;
                statListPage.value.itemCount = count;
                statListPage.value.pageCount = maxPage;
                statListPage.value.pages.set(page, { docs });
            }
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message);
        } finally {
            loadingBar.finish();
        }
    };

    const fetchEvents = async (eventId, msgPopup, loadingBar) => {
        try {
            loadingBar.start();
            const response = await fetch('index.php?option=com_semantycanm&task=Stat.getEvents&eventid=' + eventId);
            const respData = await response.json();
            if (respData.success && respData.data) {
                console.log('resp', respData.data);
                eventListPage.value.docs[eventId] = respData.data.docs;
            }
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message);
        } finally {
            loadingBar.finish();
        }
    };

    const deleteDocs = async (value, msgPopup, loadingBar) => {
        // Implement the deleteDocs action
    };

    return {
        newsletterListPage,
        statListPage,
        eventListPage,
        getPagination,
        getCurrentPage,
        fetchNewsletterData,
        fetchStatisticsData,
        fetchEvents,
        deleteDocs
    };
});
