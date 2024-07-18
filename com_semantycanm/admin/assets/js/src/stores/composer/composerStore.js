import {defineStore} from 'pinia';
import {computed, ref} from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";
import NewsletterApiManager from "../newsletter/NewsletterApiManager";
import SourceEntity from "../../utils/SourceEntity"

const ARTICLE_URL = 'index.php?option=com_semantycanm&task=Article';

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
    const articlesPage = ref({ docs: [] });
    const isLoading = ref(false);
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const articleOptions = computed(() => {
        return articlesPage.value.docs.map(doc => new SourceEntity(doc.id, doc.title, doc.category, doc.url, doc.intro));
    });

    async function fetchNewsletter(id) {
        const manager = new NewsletterApiManager(msgPopup, loadingBar);
        try {
            loadingBar.start();
            const resp = await manager.fetch(id);
            const respData = resp.data;
            console.log(respData);
            if (respData) {
                const newsletterData = {
                    id: respData.id,
                    regDate: respData.regDate,
                    templateId: respData.templateId,
                    customFieldsValues: respData.customFieldsValues,
                    articlesIds: respData.articlesIds,
                    isTest: respData.isTest,
                    mailingListIds: respData.mailingList,
                    testEmail: respData.testEmail,
                    messageContent: respData.content,
                    subject: respData.subject,
                    useWrapper: respData.useWrapper,
                };
                newsletterDoc.value = newsletterData;
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

    async function searchArticles(searchTerm) {
        try {
            const url = `${ARTICLE_URL}.search&q=${encodeURIComponent(searchTerm)}&cb=${new Date().getTime()}`;
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

    return {
        newsletterDoc,
        articlesPage,
        articleOptions,
        isLoading,
        fetchNewsletter,
        searchArticles,
    };
});