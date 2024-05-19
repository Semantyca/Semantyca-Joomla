import {defineStore} from 'pinia';

export const useTemplateStore = defineStore('template', {
    state: () => ({
        currentTemplate: {
            id: 0,
            name: '',
            type: '',
            description: '',
            content: '',
            wrapper: '',
            isDefault: false,
            customFields: []        
        },
        templateMap: {},
        availableCustomFields: {},
        pagination: {
            currentPage: 1,
            itemsPerPage: 10,
            totalItems: 0,
            totalPages: 0
        },
        cache: {
            templateMap: null,
            expiration: 0
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
            this.currentTemplate.customFields.push({...defaultFieldStructure, ...newField});
        },
        removeCustomField(index) {
            if (index >= 0 && index < this.currentTemplate.customFields.length) {
                this.currentTemplate.customFields.splice(index, 1);
            }
        }
    }
});
