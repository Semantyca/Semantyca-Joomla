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
        async updateFormCustomFields(message) {
            this.formCustomFields = {};
            const templateStore = useTemplateStore();
            await templateStore.getTemplate('classic', message);
            this.processFormCustomFields(templateStore.doc.availableCustomFields);
        },
        async fetchArticles(searchTerm, message) {
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

            } catch (error) {
                message.error(error);
            } finally {

            }
        }
    }
});
