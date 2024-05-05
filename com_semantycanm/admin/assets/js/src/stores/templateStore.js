import {defineStore} from 'pinia';
import {setCurrentTemplate} from './utils/fieldUtilities';

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
            customFields: [],
            availableCustomFields: {}
        },
        templateMap: {},
        pagination: {
            currentPage: 1,
            itemsPerPage: 10,
            totalItems: 0,
            totalPages: 0
        }
    }),
    getters: {
        templateOptions: (state) => Object.keys(state.templateMap).map(key => ({
            id: key,
            name: state.templateMap[key].name
        }))
    },
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
        async getTemplates(msgPopup, currentPage = 1, itemsPerPage = 10) {
            const url = `index.php?option=com_semantycanm&task=Template.findAll&page=${currentPage}&limit=${itemsPerPage}`;
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Failed to fetch templates, HTTP status = ${response.status}`);
                }
                const jsonResponse = await response.json();
                if (jsonResponse.success && jsonResponse.data) {
                    this.templateMap = jsonResponse.data.templates.reduce((acc, template) => {
                        acc[template.id] = template;
                        return acc;
                    }, {});
                    this.pagination = {
                        currentPage: jsonResponse.data.current,
                        itemsPerPage: itemsPerPage,
                        totalItems: jsonResponse.data.count,
                        totalPages: jsonResponse.data.maxPage
                    };
                    const defaultTemplateId = Object.keys(this.templateMap).find(id => this.templateMap[id].isDefault);
                    if (defaultTemplateId) {
                        setCurrentTemplate(this, defaultTemplateId);
                    }
                } else {
                    throw new Error('Failed to fetch templates: No data returned');
                }
            } catch (error) {
                msgPopup.error('Error fetching templates: ' + error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
            }
        }
    }
});
