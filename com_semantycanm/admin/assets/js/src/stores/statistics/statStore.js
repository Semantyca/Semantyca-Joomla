import {defineStore} from 'pinia';
import StatApiManager from "./StatApiManager";
import NewsletterApiManager from "../newsletter/NewsletterApiManager";

export const useStatStore = defineStore('stat', {
    state: () => ({
        newsletterListPage: {
            pageSize: 10,
            itemCount: 0,
            pageCount: 1,
            page: 1,
            pages: new Map(),
        },
        statListPage: {
            pageSize: 10,
            itemCount: 0,
            pageCount: 1,
            page: 1,
            pages: new Map(),
        },
        eventListPage: {
            docs: {}
        }
    }),
    getters: {
        getPagination() {
            return {
                page: this.newsletterListPage.page,
                pageSize: this.newsletterListPage.pageSize,
                itemCount: this.newsletterListPage.itemCount,
                pageCount: this.newsletterListPage.pageCount,
                size: 'large',
                showSizePicker: true,
                pageSizes: [10, 20, 50]
            };
        },
        getCurrentPage() {
            const pageData = this.newsletterListPage.pages.get(this.newsletterListPage.page);
            return pageData ? pageData.docs : [];
        }
    },
    actions: {
        async fetchNewsletterData(page, size, msgPopup, loadingBar) {
            try {
                loadingBar.start();
                const manager = new NewsletterApiManager(msgPopup, loadingBar);
                const respData = await manager.fetch(page, size);
                if (respData.success && respData.data) {
                    const { docs, count, maxPage, current } = respData.data;
                    this.newsletterListPage.page = current;
                    this.newsletterListPage.pageSize = size;
                    this.newsletterListPage.itemCount = count;
                    this.newsletterListPage.pageCount = maxPage;
                    this.newsletterListPage.pages.set(page, { docs });
                }
            } catch (error) {
                loadingBar.error()
                msgPopup.error(error.message);
            } finally {
                loadingBar.finish()
            }
        },
        async fetchStatisticsData(page, size, msgPopup, loadingBar) {
            try {
                loadingBar.start();
                const manager = new StatApiManager(msgPopup, loadingBar);
                const respData = await manager.fetch(page, size);
                if (respData.success && respData.data) {
                    const { docs, count, maxPage, current } = respData.data;
                    this.statListPage.page = current;
                    this.statListPage.pageSize = size;
                    this.statListPage.itemCount = count;
                    this.statListPage.pageCount = maxPage;
                    this.statListPage.pages.set(page, { docs });
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
        },
        async deleteDocs  (value, msgPopup, loadingBar) {

        }
    }
});
