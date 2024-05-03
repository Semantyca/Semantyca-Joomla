export async function saveTemplate(templateStore, msgPopup) {
    if (!templateStore.doc.id) {
        msgPopup.error("No template ID provided.");
        return;
    }
    try {
        await templateStore.saveTemplate(msgPopup);
        msgPopup.success("Template saved successfully.");
    } catch (error) {
        msgPopup.error("Error saving template: " + error.msgPopup);
    }
}

export async function deleteCurrentTemplate(templateStore, msgPopup) {
    try {
        const templateName = templateStore.doc.name;
        await templateStore.deleteTemplate(msgPopup);
        msgPopup.success(`Template "${templateName}" deleted successfully.`);
    } catch (error) {
        msgPopup.error("Error deleting template: " + templateStore.doc.name);
    }
}

export function exportTemplate(templateStore) {
    const filename = `${templateStore.doc.name || 'template'}.json`;
    const jsonStr = JSON.stringify(templateStore.doc, (key, value) => {
        if (key === "id" || key === "availableCustomFields" || key === "regDate") return undefined;
        return value;
    }, 2);
    const blob = new Blob([jsonStr], {type: "application/json"});
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.download = filename;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

export function importTemplate(templateStore, msgPopup) {
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = '.json';
    fileInput.onchange = async (e) => {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = async (e) => {
                try {
                    const jsonObj = JSON.parse(e.target.result);
                    templateStore.setTemplate(jsonObj);
                    await templateStore.saveTemplate(msgPopup, true);
                    msgPopup.success('Template imported and saved successfully.');
                } catch (err) {
                    msgPopup.error('Failed to import template: ' + err.msgPopup);
                }
            };
            reader.readAsText(file);
        }
    };
    fileInput.click();
}

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


