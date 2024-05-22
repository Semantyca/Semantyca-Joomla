import {defineStore} from 'pinia';
import {adaptField, processFormCustomFields} from "./utils/fieldUtilities";

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
        setCurrentTemplate(templateDoc) {
            this.currentTemplate.id = templateDoc.id;
            this.currentTemplate.name = templateDoc.name;
            this.currentTemplate.type = templateDoc.type;
            this.currentTemplate.description = templateDoc.description;
            this.currentTemplate.content = templateDoc.content;
            this.currentTemplate.wrapper = templateDoc.wrapper;
            this.currentTemplate.isDefault = templateDoc.isDefault;
            this.currentTemplate.customFields = templateDoc.customFields;

            this.availableCustomFields = processFormCustomFields(
                templateDoc.customFields.filter(field => field.isAvailable === 1),
                adaptField
            );
        },
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
