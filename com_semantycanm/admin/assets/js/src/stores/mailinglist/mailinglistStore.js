import {defineStore} from 'pinia';
import {computed} from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";
import MailingListApiManager from './MailingListApiManager';
import PaginatedData from '../PaginatedData';
import {createCache} from "../../utils/cacheUtil";

export const useMailingListStore = defineStore('mailingList', () => {
    const mailingListPage = new PaginatedData();
    const userGroupsPage = new PaginatedData();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const cache = createCache();

    const getPagination = computed(() => ({
        page: mailingListPage.page.value,
        pageSize: mailingListPage.pageSize.value,
        itemCount: mailingListPage.itemCount.value,
        pageCount: mailingListPage.pageCount.value
    }));

    const getMailingListPage = computed(() => mailingListPage.getCurrentPageData());

    const getUserGroupsOptions = computed(() =>
        userGroupsPage.getAllDocs().map(group => ({
            label: group.title,
            value: group.id
        }))
    );

    const getMailingListOptions = computed(() =>
        mailingListPage.getAllDocs().map(group => ({
            label: group.name,
            value: group.id
        }))
    );

    async function fetchMailingList(currentPage, size = 10, forceRefresh = false) {
        if (!mailingListPage.pages.value.get(currentPage) || forceRefresh) {
            const manager = new MailingListApiManager();
            const respData = await manager.fetch(currentPage, size);
            if (respData.status === 'success' && respData.data) {
                mailingListPage.updateData(respData.data);
                mailingListPage.setPageSize(size);
            }
        }
        mailingListPage.setPage(currentPage);
        return mailingListPage.getCurrentPageData();
    }

    async function getDetails(id, detailed = false) {
        const cachedDoc = cache.get(id);
        if (cachedDoc) {
            return cachedDoc;
        }

        const manager = new MailingListApiManager();
        const details = await manager.fetchDetails(id, detailed);

        if (details) {
            cache.set(id, details);
        }

        return details;
    }

    async function saveList(model) {
        const mailingListName = model.groupName;
        const listItems = model.selectedGroups;
        const manager = new MailingListApiManager();
        return await manager.upsert(mailingListName, listItems, model.id);
    }

    async function deleteMailingList(ids) {
        const manager = new MailingListApiManager();
        await manager.delete(ids);
    }

    async function fetchUserGroupsList() {
        loadingBar.start();
        try {
            const url = `index.php?option=com_semantycanm&task=UserGroup.find`;
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Network response was not 200`);
            }
            const json = await response.json();
            userGroupsPage.updateData(json.data);
        } catch (error) {
            loadingBar.error();
            msgPopup.error(error.message, {
                closable: true,
                duration: 10000
            });
        } finally {
            loadingBar.finish();
        }
    }

    return {
        mailingListPage,
        userGroupsPage,
        getPagination,
        getMailingListPage,
        fetchMailingList,
        getDetails,
        saveList,
        deleteMailingList,
        getUserGroupsOptions,
        getMailingListOptions,
        fetchUserGroupsList
    };
});
