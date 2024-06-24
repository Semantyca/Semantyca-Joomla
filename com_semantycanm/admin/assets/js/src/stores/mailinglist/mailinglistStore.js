import { defineStore } from 'pinia';
import MailingListApiManager from './MailingListApiManager';
//import { globalProperties } from "../../main";
import { ref, computed } from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";

export const useMailingListStore = defineStore('mailingList', () => {
    const page = ref(1);
    const pageSize = ref(10);
    const itemCount = ref(0);
    const pageCount = ref(1);
    const pages = ref(new Map());
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const getPagination = computed(() => {
        return {
            page: page.value,
            pageSize: pageSize.value,
            itemCount: itemCount.value,
            pageCount: pageCount.value
        };
    });

    const getCurrentPage = computed(() => {
        const pageData = pages.value.get(page.value);
        return pageData ? pageData.docs : [];
    });

    const firstPageOptions = computed(() => {
        const firstPageData = pages.value.get(1);
        if (firstPageData) {
            return firstPageData.docs.map(doc => ({
                label: doc.name, // Adjust based on the actual structure of your doc objects
                value: doc.id    // Adjust based on the actual structure of your doc objects
            }));
        }
        return [];
    });

    async function getDocs(currentPage, size = 10, forceRefresh = false, msgPopup, loadingBar) {
        if (!pages.value.get(currentPage) || forceRefresh) {
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(currentPage, size);

            if (respData.success && respData.data) {
                const { docs, count, maxPage, current } = respData.data;
                page.value = current;
                pageSize.value = size;
                itemCount.value = count;
                pageCount.value = maxPage;
                pages.value.set(currentPage, { docs });
            }
        }
        if (page.value !== currentPage) {
            page.value = currentPage;
        }
        return pages.value.get(currentPage)?.docs || [];
    }

    async function getDetails(id, msgPopup, loadingBar, detailed = false) {
        const manager = new MailingListApiManager(msgPopup, loadingBar);
        return await manager.fetchDetails(id, detailed);
    }

    async function saveList(model, id, msgPopup, loadingBar) {
        loadingBar.start();
        try {
            const mailingListName = model.groupName;
            const listItems = model.selectedGroups.map(group => group.id);
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            await manager.upsert(mailingListName, listItems, id);
        } catch (error) {
            msgPopup.error(error.message, {
                closable: true,
                //duration: globalProperties.$errorTimeout
            });
        } finally {
            loadingBar.finish();
        }
    }

    async function deleteDocs(ids, msgPopup, loadingBar) {
        const manager = new MailingListApiManager(msgPopup, loadingBar);
        await manager.delete(ids);
    }

    return {
        page,
        pageSize,
        itemCount,
        pageCount,
        pages,
        getPagination,
        getCurrentPage,
        firstPageOptions,
        getDocs,
        getDetails,
        saveList,
        deleteDocs
    };
});
