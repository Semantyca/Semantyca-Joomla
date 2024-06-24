import { defineStore } from 'pinia';
import { useMessageTemplateStore } from '../template/messageTemplateStore';
import TemplateManager from "../template/TemplateManager";
import DynamicBuilder from "../../utils/DynamicBuilder";
import { ref, computed } from 'vue';
import {useLoadingBar, useMessage} from "naive-ui";

export const useComposerStore = defineStore('composer', () => {
    const listPage = ref({ docs: [] });
    const articlesCache = ref([]);
    const selectedArticles = ref([]);
    const editorCont = ref('');
    const isLoading = ref(false);
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const templateStore = useMessageTemplateStore();

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

    async function fetchArticles(searchTerm, msgPopup, forceRefresh = false) {
        const cacheBuster = forceRefresh ? `&cb=${new Date().getTime()}` : "";

        if (searchTerm === "" && articlesCache.value.length > 0 && !forceRefresh) {
            listPage.value.docs = articlesCache.value;
            return;
        }

        try {
            const url = `index.php?option=com_semantycanm&task=Article.search&q=${encodeURIComponent(searchTerm)}${cacheBuster}`;
            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
            }

            const data = await response.json();
            listPage.value.docs = data.data.map(a => ({
                id: a.id,
                title: a.title,
                url: a.url,
                category: a.category,
                intro: encodeURIComponent(a.introtext)
            }));

            if (searchTerm === "") {
                articlesCache.value = listPage.value.docs;
            }

        } catch (error) {
            msgPopup.error(error, {
                closable: true,
                duration: this.$errorTimeout
            });
        }
    }

    async function fetchEverything(searchTerm, forceRefresh = false) {
        await updateFormCustomFields();
        await fetchArticles(searchTerm, msgPopup, forceRefresh);
    }

    return {
        listPage,
        articlesCache,
        selectedArticles,
        editorCont,
        isLoading,
        cont,
        updateFormCustomFields,
        fetchArticles,
        fetchEverything
    };
});
