import DynamicBuilder from "../utils/DynamicBuilder";

export function buildDynamicContent(customFields, templateDoc) {
    const dynamicBuilder = new DynamicBuilder(templateDoc);

    Object.keys(customFields).forEach((key) => {
        const field = customFields[key];
        dynamicBuilder.addVariable(field.name, field.defaultValue);
    });

    return dynamicBuilder.getWrappedContent();
}
