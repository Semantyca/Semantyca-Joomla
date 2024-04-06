import {defineStore} from 'pinia';
import {useComposerStore} from './composerStore';

export const useTemplateStore = defineStore('template', {
    state: () => ({
        doc: {
            id: 0,
            name: '',
            html: '',
            wrapper: '',
            customFields: [],
            availableCustomFields: []
        }
    }),
    actions: {
        addCustomField(newField) {
            const defaultFieldStructure = {
                name: '',
                type: '',
                caption: '',
                defaultValue: '',
                isAvailable: 0,
            };
            this.doc.customFields.push({...defaultFieldStructure, ...newField});
        },
        removeCustomField(index) {
            if (index >= 0 && index < this.doc.customFields.length) {
                this.doc.customFields.splice(index, 1);
            }
        },
        setTemplate(data) {
            this.$state = {...this.$state, ...data};
        },
        async getTemplate(name, message) {
            try {
                startLoading('loadingSpinner');
                const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Network response was not ok for name ${name}`);
                }
                const {data} = await response.json();
                this.doc.id = data.id;
                this.doc.name = data.name;
                this.doc.html = data.content;
                this.doc.wrapper = data.wrapper;
                this.doc.customFields = data.customFields;
                this.doc.availableCustomFields = this.doc.customFields.filter(field => field.isAvailable === 1);
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async saveTemplate(message) {
            startLoading('loadingSpinner');
            const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(this.doc.id)}`;
            const data = {
                doc: this.doc
            };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                message.success('Template saved successfully');
                const composerStore = useComposerStore();
                await composerStore.updateFormCustomFields(message);
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
