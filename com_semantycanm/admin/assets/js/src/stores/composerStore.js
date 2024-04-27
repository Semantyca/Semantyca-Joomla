import {defineStore} from 'pinia';
import {useTemplateStore} from './templateStore';

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
        processFormCustomFields(rawFields, message) {
            this.formCustomFields = rawFields.reduce((acc, field) => {
                if (field.isAvailable === 1) {
                    const key = field.name;
                    acc[key] = this.adaptField(field);
                }
                return acc;
            }, {});
        },

        adaptField(field) {
            switch (field.type) {
                case 503:
                    try {
                        const parsedValue = JSON.parse(field.defaultValue);
                        return {
                            ...field,
                            defaultValue: Array.isArray(parsedValue) ? parsedValue : []
                        };
                    } catch (error) {
                        return {
                            ...field,
                            defaultValue: []
                        };
                    }
                case 504:
                    return {
                        ...field,
                        defaultValue: field.defaultValue.replace(/^"|"$/g, "")
                    };
                case 501:
                    return {
                        ...field,
                        defaultValue: Number(field.defaultValue)
                    };
                default:
                    return {...field};
            }
        },

        async updateFormCustomFields(message) {
            this.formCustomFields = {};
            const templateStore = useTemplateStore();
            await templateStore.getTemplate('classic', message);
            const availableCustomFields = templateStore.doc.customFields.filter(field => field.isAvailable === 1);
            this.processFormCustomFields(availableCustomFields, message);
        },

        async fetchArticles(searchTerm, message) {
            if (searchTerm === "" && this.articlesCache.length > 0) {
                this.listPage.docs = this.articlesCache;
                return;
            }

            await this.updateFormCustomFields(message);

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
                message.error(error);
            } finally {

            }
        }
    }
});
