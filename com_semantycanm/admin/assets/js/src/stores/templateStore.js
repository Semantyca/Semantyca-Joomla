import {defineStore} from 'pinia';

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
        }
    }
});
