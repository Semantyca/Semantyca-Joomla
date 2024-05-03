import {defineStore} from 'pinia';
import {useTemplateStore} from './templateStore';
import {adaptField} from "./utils/fieldUtilities";

export const useComposerStore = defineStore('composer', {
    state: () => ({
        listPage: {
            docs: []
        },
        articlesCache: [],
        selectedArticles: [],
        editorCont: '',
        isLoading: false,
        formCustomFields: {}
    }),
    actions: {
        processFormCustomFields(rawFields, msgPopup) {
            this.formCustomFields = rawFields.reduce((acc, field) => {
                if (field.isAvailable === 1) {
                    const key = field.name;
                    acc[key] = adaptField(field);
                }
                return acc;
            }, {});
        },
        async updateFormCustomFields(msgPopup) {
            this.formCustomFields = {};
            const templateStore = useTemplateStore();
            await templateStore.getTemplates(msgPopup);
            const availableCustomFields = templateStore.doc.customFields.filter(field => field.isAvailable === 1);
            this.processFormCustomFields(availableCustomFields, msgPopup);
        },

        async fetchEverything(searchTerm, msgPopup) {
            if (searchTerm === "" && this.articlesCache.length > 0) {
                this.listPage.docs = this.articlesCache;
                return;
            }

            await this.updateFormCustomFields(msgPopup);

            try {
                const url = `index.php?option=com_semantycanm&task=Article.search&q=${encodeURIComponent(searchTerm)}`;
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
                msgPopup.error(error);
            } finally {

            }
        }
    }
});
