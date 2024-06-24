export function setCurrentTemplate(templateStore, defaultTemplateId) {
    const templateDoc = templateStore.templatesPage.value.pages(defaultTemplateId);

    templateStore.currentTemplate.key = templateDoc.key;
    templateStore.currentTemplate.name = templateDoc.name;
    templateStore.currentTemplate.type = templateDoc.type;
    templateStore.currentTemplate.description = templateDoc.description;
    templateStore.currentTemplate.content = templateDoc.content;
    templateStore.currentTemplate.wrapper = templateDoc.wrapper;
    templateStore.currentTemplate.isDefault = templateDoc.isDefault;
    templateStore.currentTemplate.customFields = templateDoc.customFields;
    templateStore.availableCustomFields = processFormCustomFields(templateDoc.customFields.filter(field => field.isAvailable === 1), adaptField);
}
