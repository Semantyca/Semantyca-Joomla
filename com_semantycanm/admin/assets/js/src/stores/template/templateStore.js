import {defineStore} from 'pinia';
import {ref, computed} from 'vue';
import MessageTemplateApiManager from "./MessageTemplateApiManager";
import {useLoadingBar, useMessage} from "naive-ui";
import TemplateManager from "./TemplateManager";
import {createCache} from "../../utils/cacheUtil";
import PaginatedData from "../PaginatedData";

const BASE_URL = 'index.php?option=com_semantycanm&task=Template';

export const useTemplateStore = defineStore('templates', () => {
    const listPage = new PaginatedData();
    const documentPage = new PaginatedData();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const cache = createCache();

    const templateDoc = ref({
        id: 0,
        name: '',
        type: '',
        description: '',
        content: '',
        wrapper: '',
        isDefault: false,
        customFields: [],
        cacheTime: null
    });
    const appliedTemplateDoc = ref({
        id: 0,
        name: 'no template',
        type: '',
        description: '',
        content: '',
        wrapper: '',
        isDefault: false,
        customFields: []
    });
    const templateMap = ref(new Map());
    const availableCustomFields = ref({});
    const getCurrentPage = computed(() => listPage.getCurrentPageData());
    const getPagination = computed(() => ({
        page: listPage.page.value,
        pageSize: listPage.pageSize.value,
        itemCount: listPage.itemCount.value,
        pageCount: listPage.pageCount.value
    }));

    const fetchTemplates = async (page, size) => {
        try {
            const response = await fetch(`${BASE_URL}.findAll&page=${page}&limit=${size}`);
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            if (result.data) {
                console.log(result.data);
                listPage.updateData(result.data);
                listPage.setPageSize(size);
            }
        } catch (error) {
            console.error(error);
        }
    };

    const templateSelectOptions = computed(() => {
        const options = [];
        const allDocs = listPage.getAllDocs();
        console.log(allDocs);
        allDocs.forEach(template => {
            options.push({
                label: template.name,
                key: template.id
            });
        });
        return options;
    });

    async function fetchTemplate(id) {
        const cachedTemplate = cache.get(id);
        if (cachedTemplate) {
            templateDoc.value = cachedTemplate;
            return;
        }

        try {
            loadingBar.start();
            const response = await new MessageTemplateApiManager(msgPopup, loadingBar).fetchTemplate(id);
            const data = response.data;
            if (data) {
                templateDoc.value = data;
                cache.set(id, data);
            } else {
                throw new Error('Template not found');
            }
        } catch (error) {
            msgPopup.error('Failed to fetch template: ' + error.message);
        } finally {
            loadingBar.finish();
        }
    }


    async function saveTemplate(template, id) {
        try {
            const manager = new TemplateManager(this, msgPopup, loadingBar);
            await manager.saveTemplate(template, id);
            cache.delete(id);
        } catch (error) {
            console.error(error);
        }
    }

    const applyTemplateById = (id) => {
        const allDocs = listPage.getAllDocs();
        const templateDoc = allDocs.find(document => document.id === id);
        console.log(templateDoc);
        appliedTemplateDoc.value = {...templateDoc};
        const customFields = appliedTemplateDoc.value.customFields.filter(field => field.isAvailable === 1);
        availableCustomFields.value = processFormCustomFields(customFields, adaptField);
    };

    function processFormCustomFields(availableFields, adaptField) {
        return availableFields.reduce((acc, field) => {
            if (field.isAvailable === 1) {
                const key = field.name;
                acc[key] = adaptField(field);
            }
            return acc;
        }, {});
    }

    const addCustomField = (newField) => {
        const defaultFieldStructure = {
            name: '',
            type: '',
            caption: '',
            defaultValue: '',
            isAvailable: 0,
        };
        templateDoc.value.customFields.push({...defaultFieldStructure, ...newField});
    };

    const removeCustomField = (index) => {
        if (index >= 0 && index < templateDoc.value.customFields.length) {
            templateDoc.value.customFields.splice(index, 1);
        }
    };


    const deleteApi = async (ids) => {
        try {
            const manager = new TemplateManager(this, msgPopup, loadingBar);
            await manager.deleteTemplates(ids);
        } catch (error) {
            console.error(error);
        }
    };

    function adaptField(field) {
        switch (field.type) {
            case 503:
                try {
                    const parsedValue = JSON.parse(field.defaultValue);
                    return {
                        ...field,
                        defaultValue: Array.isArray(parsedValue) ? parsedValue : []
                    };
                } catch (error) {
                    return {
                        ...field,
                        defaultValue: []
                    };
                }
            case 504:
                return {
                    ...field,
                    defaultValue: field.defaultValue.replace(/^"|"$/g, "")
                };
            case 501:
                return {
                    ...field,
                    defaultValue: Number(field.defaultValue)
                };
            case 521:
                try {
                    const parsedValue = JSON.parse(field.defaultValue);
                    return {
                        ...field,
                        defaultValue: Array.isArray(parsedValue) ? parsedValue : []
                    };
                } catch (error) {
                    return {
                        ...field,
                        defaultValue: []
                    };
                }
            default:
                return {...field};
        }
    }

    return {
        listPage,
        fetchTemplates,
        fetchTemplate,
        saveTemplate,
        deleteApi,
        templateDoc,
        appliedTemplateDoc,
        getCurrentPage,
        getPagination,
        templateMap,
        availableCustomFields,
        cache,
        templateSelectOptions,
        applyTemplateById,
        addCustomField,
        removeCustomField
    };
});
