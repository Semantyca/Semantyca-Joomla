import {defineStore} from 'pinia';
import {ref} from 'vue';
import StatApiManager from "./StatApiManager";
import {useLoadingBar, useMessage} from "naive-ui";

export const useStatStore = defineStore('stat', () => {
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

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

     const fetchStatisticsData = async (page, size) => {
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
        statListPage,
        eventListPage,
        fetchStatisticsData,
        fetchEvents,
        deleteDocs
    };
});
