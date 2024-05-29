
export function addCustomField(templateStore) {
    const newField = {
        type: 502,
        name: '',
        caption: '',
        defaultValue: '',
        isAvailable: 0
    };
    templateStore.addCustomField(newField);
}

export function removeCustomField(templateStore, index) {
    if (index >= 0 && index < templateStore.doc.customFields.length) {
        templateStore.removeCustomField(index);
    } else {
        console.error("Attempted to remove an invalid field index.");
    }
}

export function handleTypeChange(customFormFields, index) {
    customFormFields[index].defaultValue = '';
}


