import { defineStore } from 'pinia';
import MailingListApiManager from '../utils/MailingListApiManager';
import {globalProperties} from "../main";

export const useMailingListStore = defineStore('mailingList', {
    state: () => ({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map()
    }),
    getters: {
        getPagination() {
            return {
                page: this.page,
                pageSize: this.pageSize,
                itemCount: this.itemCount,
                pageCount: this.pageCount
            };
        },
        getCurrentPage() {
            const pageData = this.pages.get(this.page);
            return pageData ? pageData.docs : [];
        }
    },
    actions: {
        async getDocs(currentPage, size = 10, forceRefresh = false, msgPopup, loadingBar) {
            if (!this.pages.get(currentPage) || forceRefresh) {
                const manager = new MailingListApiManager(this, msgPopup, loadingBar);
                await manager.fetch(currentPage, size);
            }
            if (this.page !== currentPage) {
                this.page = currentPage;
            }
            return this.pages.get(currentPage).docs;
        },
        async getDetails(id, msgPopup, loadingBar, detailed = false) {
            const manager = new MailingListApiManager(this, msgPopup, loadingBar);
            return await manager.fetchDetails(id, detailed);
        },
        async saveList(model, id, msgPopup, loadingBar) {
            loadingBar.start();
            try {
                const mailingListName = model.groupName;
                const listItems = model.selectedGroups.map(group => group.id);
                const manager = new MailingListApiManager(mailingListName, listItems);
                await manager.upsert(mailingListName, listItems, id);
            } catch (error) {
                msgPopup.error(error.message, {
                    closable: true,
                    duration: globalProperties.$errorTimeout
                });
            } finally {
                loadingBar.finish();
            }
        },
        async deleteDocs(ids, msgPopup, loadingBar) {
            const manager = new MailingListApiManager(this, msgPopup, loadingBar);
            await manager.delete(ids);
        }
    }
});
