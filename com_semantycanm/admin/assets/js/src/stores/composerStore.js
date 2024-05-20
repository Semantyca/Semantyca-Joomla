import { defineStore } from 'pinia';
import { useTemplateStore } from './templateStore';
import TemplateManager from "../utils/TemplateManager";

export const useComposerStore = defineStore('composer', {
    state: () => ({
        listPage: {
            docs: []
        },
        articlesCache: [],
        selectedArticles: [],
        editorCont: '',
        isLoading: false,
    }),
    actions: {
        async updateFormCustomFields(msgPopup) {
            const templateStore = useTemplateStore();
            const templateManager = new TemplateManager(templateStore, msgPopup);
            await templateManager.getTemplates(msgPopup);
        },
        async fetchArticles(searchTerm, msgPopup, forceRefresh = false) {
            const cacheBuster = forceRefresh ? `&cb=${new Date().getTime()}` : "";

            if (searchTerm === "" && this.articlesCache.length > 0 && !forceRefresh) {
                this.listPage.docs = this.articlesCache;
                return;
            }

            try {
                const url = `index.php?option=com_semantycanm&task=Article.search&q=${encodeURIComponent(searchTerm)}${cacheBuster}`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
                }

                const data = await response.json();
                this.listPage.docs = data.data.map(a => ({
                    id: a.id,
                    title: a.title,
                    url: a.url,
                    category: a.category,
                    intro: encodeURIComponent(a.introtext)
                }));

                if (searchTerm === "") {
                    this.articlesCache = this.listPage.docs;
                }

            } catch (error) {
                msgPopup.error(error, {
                    closable: true,
                    duration: this.$errorTimeout
                });
            } finally {

            }
        },
        async fetchEverything(searchTerm, msgPopup, forceRefresh = false) {
            await this.updateFormCustomFields(msgPopup);
            await this.fetchArticles(searchTerm, msgPopup, forceRefresh);
        }
    }
});
