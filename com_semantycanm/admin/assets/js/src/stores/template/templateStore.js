import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import MessageTemplateApiManager from "./MessageTemplateApiManager";
import { useLoadingBar, useMessage } from "naive-ui";
import TemplateManager from "./TemplateManager";
import PaginatedData from "../PaginatedData";
import { Template } from '../../utils/Template';
import { CustomField } from '../../utils/Template';
import {handleError, handleNotOkError} from "../../utils/apiRequestHelper";

const BASE_URL = 'index.php?option=com_semantycanm&task=Template';

export const useTemplateStore = defineStore('templates', () => {
    const listPage = new PaginatedData();
    const documentPage = new PaginatedData();
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();

    const templateDoc = ref(new Template());
    const appliedTemplateDoc = ref(new Template(0, 'no template'));
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
                await handleNotOkError(msgPopup, response)
            } else {
                const result = await response.json();
                if (result.data) {
                    listPage.updateData(result.data);
                    listPage.setPageSize(size);
                }
            }
        } catch (error) {
            handleError(msgPopup, error);
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
        try {
            loadingBar.start();
            const response = await new MessageTemplateApiManager(msgPopup, loadingBar).fetchTemplate(id);
            const data = response.data;
            if (data) {
                templateDoc.value = new Template(
                    data.id,
                    data.name,
                    data.type,
                    data.description,
                    data.content,
                    data.wrapper,
                    data.isDefault,
                    data.customFields
                );
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
            const manager = new TemplateManager(this);
            await manager.saveTemplate(template, id);
        } catch (error) {
            console.error(error);
        }
    }

    const applyTemplateById = (id) => {
        const allDocs = listPage.getAllDocs();
        const doc = allDocs.find(document => document.id === id);
        console.log(doc);

        if (doc) {
            appliedTemplateDoc.value = new Template(
                doc.id,
                doc.name,
                doc.type,
                doc.description,
                doc.content,
                doc.wrapper,
                doc.isDefault,
                doc.customFields
            );

            const customFields = appliedTemplateDoc.value.customFields.filter(field => field.isAvailable === 1);
            availableCustomFields.value = processFormCustomFields(customFields, adaptField);
        } else {
            console.error(`Template with id ${id} not found`);
        }
    };

    const resetAppliedTemplate = () => {
        appliedTemplateDoc.value = new Template(0, 'no template');
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
        const defaultFieldStructure = new CustomField({});
        templateDoc.value.customFields.push(new CustomField({ ...defaultFieldStructure, ...newField }));
    };

    const removeCustomField = (index) => {
        if (index >= 0 && index < templateDoc.value.customFields.length) {
            templateDoc.value.customFields.splice(index, 1);
        }
    };

    const deleteApi = async (ids) => {
        loadingBar.start();
        try {
            const manager = new TemplateManager(this);
            await manager.deleteTemplates(ids);
        } catch (error) {
            loadingBar.error();
            console.error(error);
        } finally {
            loadingBar.finish();
        }
    };

    const setImportedTemplate = (importedTemplate) => {
        templateDoc.value = importedTemplate;
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
                return { ...field };
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
        templateSelectOptions,
        applyTemplateById,
        resetAppliedTemplate,
        addCustomField,
        removeCustomField,
        setImportedTemplate
    };
});