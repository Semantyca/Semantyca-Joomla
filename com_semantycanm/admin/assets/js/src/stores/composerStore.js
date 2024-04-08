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
        processFormCustomFields(rawFields, message) {
            this.formCustomFields = rawFields.reduce((acc, field) => {
                if (field.isAvailable === 1) {
                    const key = field.name;
                    if (field.type === 503) {
                        try {
                            const parsedValue = JSON.parse(field.defaultValue);
                            acc[key] = {
                                ...field,
                                defaultValue: Array.isArray(parsedValue) ? parsedValue : []
                            };
                        } catch (error) {
                            message.error(`Error parsing JSON for field "${field.caption}": ${error}`);
                            acc[key] = {
                                ...field,
                                defaultValue: []
                            };
                        }
                    } else if (field.type === 504) {
                        let defaultValue = field.defaultValue;
                        if (typeof defaultValue === 'string' && defaultValue.startsWith('"') && defaultValue.endsWith('"')) {
                            defaultValue = defaultValue.substring(1, defaultValue.length - 1);
                        }

                        acc[key] = {
                            ...field,
                            defaultValue: defaultValue
                        };
                    } else if (field.type === 501) {
                        acc[key] = {
                            ...field,
                            defaultValue: Number(field.defaultValue)
                        };
                    } else {
                        acc[key] = {
                            ...field
                        };
                    }
                }
                return acc;
            }, {});
        },

        async updateFormCustomFields(message) {
            this.formCustomFields = {};
            const templateStore = useTemplateStore();
            await templateStore.getTemplate('classic', message);
            const availableCustomFields = templateStore.doc.customFields.filter(field => field.isAvailable === 1);
            this.processFormCustomFields(availableCustomFields, message);
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
