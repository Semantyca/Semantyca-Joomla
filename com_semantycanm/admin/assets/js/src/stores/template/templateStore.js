import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import MessageTemplateApiManager from "./MessageTemplateApiManager";
import { useLoadingBar, useMessage } from "naive-ui";
import TemplateManager from "./TemplateManager";
import {createCache} from "../../utils/cacheUtil";

export const useTemplateStore = defineStore('templates', () => {
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const cache = createCache();
    const templatesPage = ref({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map()
    });
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
    const pagination = ref({
        currentPage: 1,
        itemsPerPage: 10,
        totalItems: 0,
        totalPages: 0
    });

    const templateSelectOptions = computed(() => {
        const options = [];
        templatesPage.value.pages.forEach((pageData, pageNumber) => {
            pageData.docs.forEach(template => {
                options.push({
                    label: template.name,
                    key: template.id
                });
            });
        });
        return options;
    });

    const getCurrentPage = computed(() => {
        const pageData = templatesPage.value.pages.get(templatesPage.value.page);
        return pageData ? pageData.docs : [];
    });

    const getPagination = computed(() => {
        return {
            page: templatesPage.value.page,
            pageSize: templatesPage.value.pageSize,
            itemCount: templatesPage.value.itemCount,
            pageCount: templatesPage.value.pageCount
        };
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
        const pageNum = 1;
        const onePage = templatesPage.value.pages.get(pageNum);
        const templateDoc = onePage.docs.find(document => document.id === id);
        appliedTemplateDoc.value = { ...templateDoc };
        const customFields = appliedTemplateDoc.value.customFields.filter(field => field.isAvailable === 1);
        availableCustomFields.value = processFormCustomFields(customFields, adaptField);
    };

    const resetAppliedTemplate = () => {
        appliedTemplateDoc.value = {
            id: 0,
            name: 'no template',
            type: '',
            description: '',
            content: '',
            wrapper: '',
            isDefault: false,
            customFields: []
        };
        availableCustomFields.value = {};
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
        templateDoc.value.customFields.push({ ...defaultFieldStructure, ...newField });
    };

    const removeCustomField = (index) => {
        if (index >= 0 && index < templateDoc.value.customFields.length) {
            templateDoc.value.customFields.splice(index, 1);
        }
    };

    const fetchTemplates = async (page, size) => {
        try {
            const manager = new MessageTemplateApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(page, size);
            if (respData.success && respData.data) {
                const { docs, count, maxPage, current } = respData.data;
                templatesPage.value.pageSize = size;
                templatesPage.value.itemCount = count;
                templatesPage.value.pageCount = maxPage;
                templatesPage.value.pageNum = current;
                templatesPage.value.pages.set(page, { docs });
            }
        } catch (error) {
            console.error(error);
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
            case 520:
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
                return { ...field };
        }
    }

    return {
        fetchTemplates,
        fetchTemplate,
        saveTemplate,
        deleteApi,
        templatesPage,
        templateDoc,
        appliedTemplateDoc,
        getCurrentPage,
        getPagination,
        templateMap,
        availableCustomFields,
        pagination,
        cache,
        templateSelectOptions,
        applyTemplateById,
        resetAppliedTemplate,
        addCustomField,
        removeCustomField
    };
});
