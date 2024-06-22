import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { adaptField, processFormCustomFields } from "../../utils/fieldUtilities";
import MessageTemplateApiManager from "./MessageTemplateApiManager";
import {useLoadingBar, useMessage} from "naive-ui";

export const useMessageTemplateStore = defineStore('messageTemplate', () => {
    const msgPopup = useMessage();
    const loadingBar = useLoadingBar();
    const messageTemplateListPage = ref({
        page: 1,
        pageSize: 10,
        itemCount: 0,
        pageCount: 1,
        pages: new Map()
    });
    const currentTemplate = ref({
        id: 0,
        name: '',
        type: '',
        description: '',
        content: '',
        wrapper: '',
        isDefault: false,
        customFields: []
    });

    const templateMap = ref({});
    const availableCustomFields = ref({});
    const pagination = ref({
        currentPage: 1,
        itemsPerPage: 10,
        totalItems: 0,
        totalPages: 0
    });

    const cache = ref({
        templateMap: null,
        expiration: 0
    });

    const getCurrentPage = computed(() => {
        const pageData = messageTemplateListPage.value.pages.get(messageTemplateListPage.value.page);
        return pageData ? pageData.docs : [];
    });

    const getPagination = computed(() =>  {
        return {
            page: messageTemplateListPage.value.page,
            pageSize: messageTemplateListPage.value.pageSize,
            itemCount: messageTemplateListPage.value.itemCount,
            pageCount: messageTemplateListPage.value.pageCount
        };
    });

    const templateOptions = computed(() =>
        Object.keys(templateMap.value).map(key => ({
            id: key,
            name: templateMap.value[key].name
        }))
    );

    const templateSelectOptions = computed(() =>
        Object.keys(templateMap.value).map(key => ({
            value: key,
            label: templateMap.value[key].name
        }))
    );

    const setCurrentTemplate = (templateDoc) => {
        currentTemplate.value.id = templateDoc.id;
        currentTemplate.value.name = templateDoc.name;
        currentTemplate.value.type = templateDoc.type;
        currentTemplate.value.description = templateDoc.description;
        currentTemplate.value.content = templateDoc.content;
        currentTemplate.value.wrapper = templateDoc.wrapper;
        currentTemplate.value.isDefault = templateDoc.isDefault;
        currentTemplate.value.customFields = templateDoc.customFields;

        availableCustomFields.value = processFormCustomFields(
            templateDoc.customFields.filter(field => field.isAvailable === 1),
            adaptField
        );
    };

    const addCustomField = (newField) => {
        const defaultFieldStructure = {
            name: '',
            type: '',
            caption: '',
            defaultValue: '',
            isAvailable: 0,
        };
        currentTemplate.value.customFields.push({ ...defaultFieldStructure, ...newField });
    };

    const removeCustomField = (index) => {
        if (index >= 0 && index < currentTemplate.value.customFields.length) {
            currentTemplate.value.customFields.splice(index, 1);
        }
    };

    const fetchFromApi = async (page, size) => {
        try {
            const manager = new MessageTemplateApiManager(msgPopup, loadingBar);
            const respData = await manager.fetch(page, size);
            if (respData.success && respData.data) {
                const {docs, count, maxPage, current} = respData.data;
                messageTemplateListPage.value.pageSize = size;
                messageTemplateListPage.value.itemCount = count;
                messageTemplateListPage.value.pageCount = maxPage;
                messageTemplateListPage.value.pageNum = current;
                messageTemplateListPage.value.pages.set(page, {docs});
            }
        } catch (error) {
            console.error(error);
        }
    };

    return {
        fetchFromApi,
        getCurrentPage,
        getPagination,
        currentTemplate,
        templateMap,
        availableCustomFields,
        pagination,
        cache,
        templateOptions,
        templateSelectOptions,
        setCurrentTemplate,
        addCustomField,
        removeCustomField
    };
});
