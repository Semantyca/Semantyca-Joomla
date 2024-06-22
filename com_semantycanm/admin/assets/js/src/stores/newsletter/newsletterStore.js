import {defineStore} from 'pinia';
import {ref, computed} from 'vue';
import MailingListApiManager from "../mailinglist/MailingListApiManager";
import {useLoadingBar, useMessage} from "naive-ui";

export const useNewsletterStore = defineStore('newsletters', () => {
    const newslettersListPage = ref({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map()
    });

    const mailingListPage = ref({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map(),
    });

    const currentNewsletterId = ref(0);
    const progress = ref({
        dispatched: 0,
        read: 0
    });

    const currentInterval = ref(1000);
    const maxInterval = ref(30000);

    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const mailingListApiManager = new MailingListApiManager(msgPopup, loadingBar);

    const getMailingListPage = computed(() => {
        const pageData = mailingListPage.value.pages.get(mailingListPage.value.page);
        return pageData ? pageData.docs : [];
    });

    const mailingListOptions = computed(() => {
        const allDocs = [];
        for (const page of mailingListPage.value.pages.values()) {
            allDocs.push(...page.docs);
        }
        return allDocs.map(doc => ({
            label: doc.name,
            value: doc.id
        }));
    });

    const getCurrentPage = computed(() => {
        const pageData = newslettersListPage.value.pages.get(newslettersListPage.value.page);
        return pageData ? pageData.docs : [];
    });

    const getPagination = computed(() =>  {
        return {
            page: newslettersListPage.value.page,
            pageSize: newslettersListPage.value.pageSize,
            itemCount: newslettersListPage.value.itemCount,
            pageCount: newslettersListPage.value.pageCount
        };
    });

    const getMailingLists = async (currentPage, size = 10) => {
        const respData = await mailingListApiManager.fetch(currentPage, size);

        if (respData.success && respData.data) {
            const {docs, count, maxPage, current} = respData.data;
            mailingListPage.value.page = current;
            mailingListPage.value.pageSize = size;
            mailingListPage.value.itemCount = count;
            mailingListPage.value.pageCount = maxPage;
            mailingListPage.value.pages.set(current, {docs});
        }

    };

    const fetchNewsLetters = async (page, size) => {
        try {
            const response = await fetch(`index.php?option=com_semantycanm&task=newsletters.findAll&page=${page}&limit=${size}`);

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const respData = await response.json();
            if (respData.success && respData.data) {
                const {docs, count, maxPage, current} = respData.data;
                newslettersListPage.value.pageSize = size;
                newslettersListPage.value.itemCount = count;
                newslettersListPage.value.pageCount = maxPage;
                newslettersListPage.value.pageNum = current;
                newslettersListPage.value.pages.set(page, {docs});
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
        } finally {

        }
    };




    const getRowByKey = (key) => {
        console.log(key);
        return newslettersListPage.value.pages.get(newslettersListPage.value.page).docs.find(doc => doc.key === key);
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
        getRowByKey
    };
});
