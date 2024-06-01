import { defineStore } from 'pinia';
import MailingListApiManager from "../mailinglist/MailingListApiManager";

export const useNewsletterStore = defineStore('newsletter', {
    state: () => ({
        docsListPage: {
            docs: []
        },
        mailingListPage: {
            page: 1,
            pageSize: 10,
            itemCount: 0,
            pageCount: 1,
            pages: new Map(),
        },
        currentNewsletterId: 0,
        progress: {
            dispatched: 0,
            read: 0
        },
        currentInterval: 1000,
        maxInterval: 30000
    }),
    getters: {
        getMailingListPage() {
            const pageData = this.mailingListPage.pages.get(this.mailingListPage.page);
            return pageData ? pageData.docs : [];
        },
        mailingListOptions() {
            const allDocs = [];
            for (const page of this.mailingListPage.pages.values()) {
                allDocs.push(...page.docs);
            }
            return allDocs.map(doc => ({
                label: doc.name,
                value: doc.id
            }));
        }
    },
    actions: {
        async getMailingLists(currentPage, size = 10, forceRefresh = false, msgPopup, loadingBar) {
            if (!this.mailingListPage.pages.get(currentPage) || forceRefresh) {
                const manager = new MailingListApiManager(msgPopup, loadingBar);
                const respData = await manager.fetch(currentPage, size);

                if (respData.success && respData.data) {
                    const { docs, count, maxPage, current } = respData.data;
                    this.mailingListPage.page = current;
                    this.mailingListPage.pageSize = size;
                    this.mailingListPage.itemCount = count;
                    this.mailingListPage.pageCount = maxPage;
                    this.mailingListPage.pages.set(currentPage, { docs });
                }
            }
            if (this.mailingListPage.page !== currentPage) {
                this.mailingListPage.page = currentPage;
            }
            return this.mailingListPage.pages.get(currentPage)?.docs || [];
        },
        async fetchNewsLetter(page, size, pagination) {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=NewsLetter.findAll&page=' + page + '&limit=' + size);

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const respData = await response.json();
                if (respData.success && respData.data) {
                    this.docsListPage.docs = respData.data.docs;
                    pagination.pageSize = size;
                    pagination.itemCount = respData.data.count;
                    pagination.pageCount = respData.data.maxPage;
                    pagination.page = respData.data.current;
                }
            } catch (error) {
                console.error(error);

            } finally {

            }
        },
        async fetchCurrentNewsLetterEvents() {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=NewsLetter.findNewsletterEvents&id=' + this.currentNewsletterId);

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const respData = await response.json();
                if (respData.success && respData.data) {
                    const dispatchedEvents = respData.data.filter(event => event.event_type === 100);
                    const dispatchedFulfilled = dispatchedEvents.filter(event => event.fulfilled === 2).length;
                    this.progress.dispatched = Math.round((dispatchedFulfilled / dispatchedEvents.length) * 100) || 0;
                    const readEvents = respData.data.filter(event => event.event_type === 101);
                    const readFulfilled = readEvents.filter(event => event.fulfilled === 2).length;
                    this.progress.read = Math.round((readFulfilled / readEvents.length) * 100) || 0;
                }
            } catch (error) {
                console.error(error);
                this.stopPolling();
            } finally {
                this.adjustPollingInterval();
            }
        },
        startPolling() {
            if (this.currentNewsletterId === 0) {
                return;
            }

            this.currentInterval = 1000;
            if (this.pollingIntervalId) {
                clearInterval(this.pollingIntervalId);
            }
            this.fetchCurrentNewsLetterEvents();
            this.setupPollingInterval();
        },
        setupPollingInterval() {
            this.pollingIntervalId = setInterval(() => {
                if (this.currentNewsletterId !== 0) {
                    this.fetchCurrentNewsLetterEvents();
                } else {
                    this.stopPolling();
                }
            }, this.currentInterval);
        },
        adjustPollingInterval() {
            let newInterval = Math.min(this.currentInterval * 1.5, this.maxInterval);
            if (newInterval !== this.currentInterval) {
                this.currentInterval = newInterval;
                console.log('Adjusted interval to:', this.currentInterval);
                clearInterval(this.pollingIntervalId);
                this.setupPollingInterval();
            }
        },
        stopPolling() {
            if (this.pollingIntervalId) {
                clearInterval(this.pollingIntervalId);
                this.pollingIntervalId = null;
            }
        },
        getRowByKey(key) {
            console.log(key);
            return this.docsListPage.docs.find(doc => doc.key === key);
        }
    }
});
