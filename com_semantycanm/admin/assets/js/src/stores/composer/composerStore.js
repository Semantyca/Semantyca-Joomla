import {defineStore} from 'pinia';
import {useMessageTemplateStore} from '../template/messageTemplateStore';
import TemplateManager from "../template/TemplateManager";
import DynamicBuilder from "../../utils/DynamicBuilder";
import {ref, computed} from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";
import MailingListApiManager from "../mailinglist/MailingListApiManager";

export const useComposerStore = defineStore('composer', () => {
    const articlesPage = ref({docs: []});
    const selectedArticles = ref([]);
    const editorCont = ref('');
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
            label: doc.title,
            value: doc.id,
            groupName: doc.category,
            articleTitle: doc.title
        }));
    });

    const cont = computed(() => {
        const dynamicBuilder = new DynamicBuilder(templateStore.currentTemplate);
        dynamicBuilder.addVariable("articles", selectedArticles.value);

        Object.keys(templateStore.availableCustomFields).forEach((key) => {
            const field = templateStore.availableCustomFields[key];
            const fieldValue = field.defaultValue;
            dynamicBuilder.addVariable(key, fieldValue);
        });

        try {
            return dynamicBuilder.buildContent();
        } catch (e) {
            msgPopup.error(e.message, {
                closable: true,
                duration: 10000
            });
            return '';
        }
    });

    async function updateFormCustomFields() {
        const templateManager = new TemplateManager(templateStore, msgPopup, loadingBar);
        await templateManager.getTemplates(true);
    }



    async function fetchArticlesApi(searchTerm) {
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

    async function fetchMailingListsApi(currentPage, size = 10, forceRefresh = false) {
        if (!mailingListPage.value.pages.get(currentPage) || forceRefresh) {
            const manager = new MailingListApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(currentPage, size);

            if (respData.success && respData.data) {
                const { docs, count, maxPage, current } = respData.data;
                mailingListPage.value.page = current;
                mailingListPage.value.pageSize = size;
                mailingListPage.value.itemCount = count;
                mailingListPage.value.pageCount = maxPage;
                mailingListPage.value.pages.set(currentPage, { docs });
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
        articlesPage,
        articleOptions,
        selectedArticles,
        editorCont,
        firstPageOptions,
        isLoading,
        cont,
        fetchArticlesApi,
        fetchMailingListsApi,
    };
});