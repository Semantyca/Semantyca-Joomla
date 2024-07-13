import {defineStore} from 'pinia';
import {ref, computed} from 'vue';
import MailingListApiManager from "../mailinglist/MailingListApiManager";
import PaginatedData from '../PaginatedData';

export const useNewsletterStore = defineStore('newsletters', () => {
    const newslettersListPage = new PaginatedData();
    const mailingListPage = new PaginatedData();
    /** @deprecated redundant */
    const currentNewsletterId = ref(0);
    const progress = ref({
        dispatched: 0,
        read: 0
    });
    const currentInterval = ref(1000);
    const maxInterval = ref(30000);
    const mailingListApiManager = new MailingListApiManager();
    const getMailingListPage = computed(() => mailingListPage.getCurrentPageData());
    const mailingListOptions = computed(() =>
        mailingListPage.getAllDocs().map(doc => ({
            label: doc.name,
            value: doc.id
        }))
    );
    const getCurrentPage = computed(() => newslettersListPage.getCurrentPageData());
    const getPagination = computed(() => ({
        page: newslettersListPage.page.value,
        pageSize: newslettersListPage.pageSize.value,
        itemCount: newslettersListPage.itemCount.value,
        pageCount: newslettersListPage.pageCount.value
    }));
    const getMailingLists = async (currentPage, size = 10) => {
        const respData = await mailingListApiManager.fetch(currentPage, size);
        if (respData.success && respData.data) {
            mailingListPage.updateData(respData.data);
            mailingListPage.setPageSize(size);
        }
    };
    const fetchNewsLetters = async (page, size) => {
        try {
            const response = await fetch(`index.php?option=com_semantycanm&task=newsletters.findAll&page=${page}&limit=${size}`);

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            if (result.data) {
                newslettersListPage.updateData(result.data);
                newslettersListPage.setPageSize(size);
            }
        } catch (error) {
            console.error(error);
        }
    };

    const fetchCurrentNewsLetterEvents = async () => {
        try {
            const response = await fetch(`index.php?option=com_semantycanm&task=NewsLetter.findNewsletterEvents&id=${currentNewsletterId.value}`);

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const respData = await response.json();
            if (respData.success && respData.data) {
                const dispatchedEvents = respData.data.filter(event => event.event_type === 100);
                const dispatchedFulfilled = dispatchedEvents.filter(event => event.fulfilled === 2).length;
                progress.value.dispatched = Math.round((dispatchedFulfilled / dispatchedEvents.length) * 100) || 0;
                const readEvents = respData.data.filter(event => event.event_type === 101);
                const readFulfilled = readEvents.filter(event => event.fulfilled === 2).length;
                progress.value.read = Math.round((readFulfilled / readEvents.length) * 100) || 0;
            }
        } catch (error) {
            console.error(error);
        }
    };

    const getRowByKey = (key) => {
        return newslettersListPage.getCurrentPageData().find(doc => doc.key === key);
    };

    const deleteNewsletters = async (ids) => {
        try {
            const response = await fetch('index.php?option=com_semantycanm&task=newsletters.delete', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ids}),
            });

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }

            const respData = await response.json();

            if (respData.success) {
                await fetchNewsLetters(newslettersListPage.page.value, newslettersListPage.pageSize.value);
            }
        } catch (error) {
            console.error(error);
            throw new Error('An error occurred while deleting newsletters.');
        }
    };

    return {
        getCurrentPage,
        getPagination,
        newslettersListPage,
        mailingListPage,
        currentNewsletterId,
        progress,
        currentInterval,
        maxInterval,
        getMailingListPage,
        mailingListOptions,
        getMailingLists,
        fetchNewsLetters,
        fetchCurrentNewsLetterEvents,
        getRowByKey,
        deleteNewsletters
    };
});