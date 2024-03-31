import {defineStore} from 'pinia';
import {useTemplateStore} from './templateStore';

export const useComposerStore = defineStore('composer', {
    state: () => ({
        listPage: {
            docs: []
        },
        selectedArticles: [],
        editorCont: '',
        isLoading: false,
        formCustomFields: {}
    }),
    actions: {
        processFormCustomFields(rawFields) {
            this.formCustomFields = rawFields.reduce((acc, field) => {
                const key = field.name;
                console.log(key);
                if (field.type === 503) {
                    acc[key] = {
                        ...field,
                        defaultValue: [...field.defaultValue]
                    };
                } else {
                    acc[key] = {
                        ...field
                    };
                }
                return acc;
            }, {});
        },

        async fetchArticles(searchTerm, message) {
            this.isLoading = true;
            if (Object.keys(this.formCustomFields).length === 0) {
                const templateStore = useTemplateStore();
                if (templateStore.doc.availableCustomFields.length === 0) {
                    await templateStore.getTemplate('classic', message);
                }
                this.processFormCustomFields(templateStore.doc.availableCustomFields);
            }

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

            } catch (error) {
                console.error(`Problem fetching articles:`, error);
            } finally {
                this.isLoading = false;
            }
        }
    }
});
