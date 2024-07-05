import {defineStore} from 'pinia';
import {useMessageTemplateStore} from '../template/messageTemplateStore';
import TemplateManager from "../template/TemplateManager";
import {computed, ref} from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";
import MailingListApiManager from "../mailinglist/MailingListApiManager";
import NewsletterApiManager from "../newsletter/NewsletterApiManager";
import MessageTemplateApiManager from "../template/MessageTemplateApiManager";

export const useComposerStore = defineStore('composer', () => {
    const newsletterDoc = ref({
        id: null,
        regDate: null,
        subject: '',
        useWrapper: false,
        templateId: null,
        customFieldsValues: {},
        articlesIds: [],
        isTest: false,
        mailingListIds: [],
        testEmail: '',
        messageContent: '',
        modifiedDate: null
    });
    const templateDoc = ref({
        id: null,
        regDate: null,
        modifiedDate: null
    });
    const articlesPage = ref({docs: []});
    const mailingListPage = ref({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map()
    });
    const isLoading = ref(false);
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const templateStore = useMessageTemplateStore();

    const articleOptions = computed(() => {
        return articlesPage.value.docs.map(doc => ({
            value: doc.id,
            category: doc.category,
            title: doc.title,
            url: doc.url,
            intro: doc.intro
        }));
    });


    async function updateFormCustomFields() {
        const templateManager = new TemplateManager(templateStore, msgPopup, loadingBar);
        await templateManager.getTemplates(true);
    }

    async function fetchNewsletter(id) {
        const manager = new NewsletterApiManager(msgPopup, loadingBar);
        try {
            loadingBar.start();
            const resp = await manager.fetch(id);
            const respData = resp.data;
            if (respData) {
                newsletterDoc.value = {
                    id: respData.id,
                    regDate: respData.regDate,
                    templateId: respData.templateId,
                    customFieldsValues: respData.customFieldsValues,
                    articlesIds: respData.articlesIds,
                    isTest: respData.isTest,
                    mailingListIds: respData.mailingList,
                    testEmail: respData.testEmail,
                    messageContent: respData.content,  // Note: 'content' in DTO maps to 'messageContent'
                    subject: respData.subject,
                    useWrapper: respData.useWrapper
                };
            } else {
                throw new Error('Newsletter not found');
            }
        } catch (error) {
            console.error('Error fetching newsletter:', error);
            msgPopup.error('Failed to fetch newsletter: ' + error.message);
        } finally {
            loadingBar.finish();
        }
    }

    async function fetchTemplate(id) {
        try {
            const manager = new MessageTemplateApiManager(msgPopup, loadingBar);
            const respData = await manager.fetchTemplate(id);
            if (respData) {
                templateDoc.value = {
                    id: respData.id,
                    regDate: respData.regDate,

                };
            } else {
                throw new Error('Newsletter not found');
            }
        } catch (error) {
            console.error(error);
        }
    };

    async function searchArticles(searchTerm) {
        try {
            const url = `index.php?option=com_semantycanm&task=Article.search&q=${encodeURIComponent(searchTerm)}&cb=${new Date().getTime()}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
            }

            const data = await response.json();
            articlesPage.value.docs = data.data.map(a => ({
                id: a.id,
                title: a.title,
                url: a.url,
                category: a.category,
                intro: encodeURIComponent(a.introtext)
            }));

        } catch (error) {
            msgPopup.error(error, {
                closable: true,
                duration: this.$errorTimeout
            });
        }
    }

    async function fetchMailingLists(currentPage, size = 10, forceRefresh = false) {
        if (!mailingListPage.value.pages.get(currentPage) || forceRefresh) {
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(currentPage, size);

            if (respData.success && respData.data) {
                const {docs, count, maxPage, current} = respData.data;
                mailingListPage.value.page = current;
                mailingListPage.value.pageSize = size;
                mailingListPage.value.itemCount = count;
                mailingListPage.value.pageCount = maxPage;
                mailingListPage.value.pages.set(currentPage, {docs});
            }
        }
        if (mailingListPage.value.page !== currentPage) {
            mailingListPage.value.page = currentPage;
        }
        return mailingListPage.value.pages.get(currentPage)?.docs || [];
    }

    const firstPageOptions = computed(() => {
        const firstPageData = mailingListPage.value.pages.get(1);
        if (firstPageData && firstPageData.docs) {
            return firstPageData.docs.map(doc => ({
                label: doc.name,
                value: doc.id
            }));
        }
        return [];
    });

    return {
        newsletterDoc,
        templateDoc,
        articlesPage,
        articleOptions,
        firstPageOptions,
        isLoading,
        fetchNewsletter,
        fetchTemplate,
        searchArticles,
        fetchMailingLists,
    };
});