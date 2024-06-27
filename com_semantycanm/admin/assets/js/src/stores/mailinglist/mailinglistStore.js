import { defineStore } from 'pinia';
import MailingListApiManager from './MailingListApiManager';
import { computed } from 'vue';
import { useLoadingBar, useMessage } from "naive-ui";
import PaginatedData from '../PaginatedData';

export const useMailingListStore = defineStore('mailingList', () => {
    const mailingListPage = new PaginatedData();
    const userGroupsPage = new PaginatedData();

    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const getPagination = computed(() => ({
        page: mailingListPage.page.value,
        pageSize: mailingListPage.pageSize.value,
        itemCount: mailingListPage.itemCount.value,
        pageCount: mailingListPage.pageCount.value
    }));

    const getCurrentPage = computed(() => mailingListPage.getCurrentPageData());

    const getUserGroupsOptions = computed(() =>
        userGroupsPage.getAllDocs().map(group => ({
            label: group.title,
            value: group.id
        }))
    );

    const getMailingListOptions = computed(() =>
        mailingListPage.getAllDocs().map(group => ({
            label: group.title,
            value: group.key
        }))
    );

    async function fetchMailingList(currentPage, size = 10, forceRefresh = false) {
        if (!mailingListPage.pages.value.get(currentPage) || forceRefresh) {
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(currentPage, size);

            if (respData.success && respData.data) {
                mailingListPage.updateData(respData.data);
                mailingListPage.setPageSize(size);
            }
        }
        mailingListPage.setPage(currentPage);
        return mailingListPage.getCurrentPageData();
    }

    async function getDetails(id, detailed = false) {
        const manager = new MailingListApiManager(msgPopup, loadingBar);
        return await manager.fetchDetails(id, detailed);
    }

    async function saveList(model) {
        loadingBar.start();
        try {
            const mailingListName = model.groupName;
            const listItems = model.selectedGroups;
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            const result = await manager.upsert(mailingListName, listItems, model.id);
            if (result.success) {
                msgPopup.success('Mailing list saved successfully');
                return true;
            } else {
                throw new Error(result.message || 'Failed to save mailing list');
            }
        } catch (error) {
            msgPopup.error(error.message, {
                closable: true,
                duration: 10000
            });
            return false;
        } finally {
            loadingBar.finish();
        }
    }

    async function deleteMailingList(ids) {
        const manager = new MailingListApiManager(msgPopup, loadingBar);
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
        getCurrentPage,
        fetchMailingList,
        getDetails,
        saveList,
        deleteMailingList,
        getUserGroupsOptions,
        getMailingListOptions,
        fetchUserGroupsList
    };
});