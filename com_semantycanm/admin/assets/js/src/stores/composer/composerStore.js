import {defineStore} from 'pinia';
import {useMessageTemplateStore} from '../template/messageTemplateStore';
import TemplateManager from "../template/TemplateManager";
import DynamicBuilder from "../../utils/DynamicBuilder";
import {ref, computed} from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";

export const useComposerStore = defineStore('composer', () => {
    const articlesPage = ref({docs: []});
    const selectedArticles = ref([]);
    const editorCont = ref('');
    const isLoading = ref(false);
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const templateStore = useMessageTemplateStore();

    const articleSelectOptions = computed(() => {
        const options = [];
        articlesPage.value.docs.forEach((doc, pageNumber) => {

            options.push({
                label: doc.title,
                value: doc.id
            });

        });
        return options;
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
            console.error(e.message);
            return '';
        }
    });

    async function updateFormCustomFields() {
        const templateManager = new TemplateManager(templateStore, msgPopup, loadingBar);
        await templateManager.getTemplates(true);
    }

    async function fetchArticles(searchTerm, msgPopup) {
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

    async function fetchEverything(searchTerm) {
        //     await updateFormCustomFields();
        await fetchArticles(searchTerm, msgPopup);
    }

    return {
        articlesPage,
        articleSelectOptions,
        selectedArticles,
        editorCont,
        isLoading,
        cont,
        fetchArticles,
        fetchEverything
    };
});