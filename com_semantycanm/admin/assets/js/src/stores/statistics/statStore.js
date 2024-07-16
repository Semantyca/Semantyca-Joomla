import {defineStore} from 'pinia';
import {computed} from 'vue';
import StatApiManager from "./StatApiManager";
import {useLoadingBar, useMessage} from "naive-ui";
import PaginatedData from "../PaginatedData";

const BASE_URL = 'index.php?option=com_semantycanm&task=Stat';

export const useStatStore = defineStore('stat', () => {
    const listPage = new PaginatedData();
    const eventListPage = new PaginatedData();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const getCurrentPage = computed(() => listPage.getCurrentPageData());
    const getCurrentEventPage = computed(() => eventListPage.getCurrentPageData());
    const getPagination = computed(() => ({
        page: listPage.page.value,
        pageSize: listPage.pageSize.value,
        itemCount: listPage.itemCount.value,
        pageCount: listPage.pageCount.value
    }));

    const fetchStatisticsData = async (page, size) => {
        try {
            loadingBar.start();
            const response = await fetch(`${BASE_URL}findAll&page=${page}&limit=${size}`);
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            if (result.data) {
                console.log(result.data);
                listPage.updateData(result.data);
                listPage.setPageSize(size);
            }
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message);
        } finally {
            loadingBar.finish();
        }
    };

    const fetchEvents = async (eventId) => {
        try {
            loadingBar.start();
            const response = await fetch(`${BASE_URL}.getEvents&eventid=${eventId}`);
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            if (result.data) {
                console.log(result.data);
                eventListPage.updateData(result.data);
                eventListPage.setPageSize(1000);
            }
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message);
        } finally {
            loadingBar.finish();
        }
    };

    const deleteDocs = async (ids) => {
        const manager = new StatApiManager(msgPopup, loadingBar);
        await manager.delete(ids);
    };

    return {
        getCurrentPage,
        getPagination,
        getCurrentEventPage,
        fetchStatisticsData,
        fetchEvents,
        deleteDocs
    };
});
