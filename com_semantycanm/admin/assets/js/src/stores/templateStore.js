import {defineStore} from 'pinia';
import {useComposerStore} from './composerStore';

export const useTemplateStore = defineStore('template', {
    state: () => ({
        doc: {
            id: 0,
            name: '',
            type: '',
            description: '',
            content: '',
            wrapper: '',
            customFields: []
        },
        templatesCache: {}
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
            this.doc = {...this.doc, ...data};
        },
        async getTemplate(name, message) {
            if (this.templatesCache[name]) {
                this.setTemplate(this.templatesCache[name]);
                return;
            }
            startLoading('loadingSpinner');
            const url = `index.php?option=com_semantycanm&task=Template.find&name=${encodeURIComponent(name)}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`The template \"${name}\" is not found`);
                }
                const {data} = await response.json();
                this.templatesCache[name] = data;
                this.setTemplate(data);
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async saveTemplate(message) {
            startLoading('loadingSpinner');
            const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(this.doc.id)}`;
            const data = {doc: this.doc};

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const result = await response.json();
                message.success(result.data.message);
                this.templatesCache[this.doc.name] = this.doc;
                const composerStore = useComposerStore();
                await composerStore.updateFormCustomFields(message);
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async deleteTemplate(message) {
            if (!this.doc.id) {
                message.error("Template ID is missing");
                return;
            }
            startLoading('loadingSpinner');
            const endpoint = `index.php?option=com_semantycanm&task=Template.delete&id=${encodeURIComponent(this.doc.id)}`;

            try {
                const response = await fetch(endpoint, {
                    method: 'DELETE'
                });

                if (!response.ok) {
                    throw new Error(`Failed to delete template, HTTP status = ${response.status}`);
                }
                const result = await response.json();
                message.success(result.data.message);
                delete this.templatesCache[this.doc.name];
                this.doc = {id: 0, name: '', type: '', description: '', content: '', wrapper: '', customFields: []};
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
