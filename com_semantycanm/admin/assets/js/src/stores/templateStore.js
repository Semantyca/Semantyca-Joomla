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
            isDefault: false,
            customFields: []
        },
        activeTemplCache: {},
        templatesList: [],
        pagination: {
            currentPage: 1,
            itemsPerPage: 10,
            totalItems: 0,
            totalPages: 0
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
            this.doc = {...this.doc, ...data};
        },
        async fetchTemplates(currentPage = 1, itemsPerPage = 10) {
            const url = `index.php?option=com_semantycanm&task=Template.findAll&page=${currentPage}&limit=${itemsPerPage}`;
            startLoading('loadingSpinner');
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Failed to fetch templates, HTTP status = ${response.status}`);
                }
                const jsonResponse = await response.json();

                if (jsonResponse.success && jsonResponse.data) {
                    this.templatesList = jsonResponse.data.templates;
                    this.pagination = {
                        currentPage: jsonResponse.data.current,
                        itemsPerPage: itemsPerPage,
                        totalItems: jsonResponse.data.count,
                        totalPages: jsonResponse.data.maxPage
                    };
                } else {
                    throw new Error('Failed to fetch templates: No data returned');
                }
            } catch (error) {
                console.error('Error fetching templates:', error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async getTemplate(name, message) {
            if (this.activeTemplCache[name]) {
                this.setTemplate(this.activeTemplCache[name]);
                return;
            }
            startLoading('loadingSpinner');
            const url = `index.php?option=com_semantycanm&task=Template.find&name=${encodeURIComponent(name)}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    message.warning(`The template \"${name}\" is not found`);
                } else {
                    const {data} = await response.json();
                    this.activeTemplCache[name] = data;
                    this.setTemplate(data);
                }
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async saveTemplate(message, isNew = false) {
            startLoading('loadingSpinner');

            let endpoint, method;
            if (isNew) {
                endpoint = `index.php?option=com_semantycanm&task=Template.update&id=`;
                method = 'POST';
            } else {
                endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(this.doc.id)}`;
                method = 'POST';
            }

            const data = {doc: this.doc};

            try {
                const response = await fetch(endpoint, {
                    method: method,
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const result = await response.json();
                message.success(result.data.message);
                this.activeTemplCache[this.doc.name] = this.doc;
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
                delete this.activeTemplCache[this.doc.name];
                this.doc = {id: 0, name: '', type: '', description: '', content: '', wrapper: '', customFields: []};
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
