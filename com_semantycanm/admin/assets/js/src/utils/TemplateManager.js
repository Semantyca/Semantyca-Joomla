import {setCurrentTemplate} from "../stores/utils/fieldUtilities";

class TemplateManager {
    constructor(store, msgPopup) {
        this.store = store;
        this.msgPopup = msgPopup;
        this.errorTimeout = 10000;
    }

    async saveTemplate(isNew = false) {
        const endpoint = isNew ? `index.php?option=com_semantycanm&task=Template.update&id=` :
            `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(this.store.doc.id)}`;
        const method = 'POST';
        const data = {doc: this.store.doc};

        try {
            const response = await fetch(endpoint, {
                method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            });

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            this.msgPopup.success(result.data.message);
            return result;
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        }
    }

    async deleteCurrentTemplate() {
        const endpoint = `index.php?option=com_semantycanm&task=Template.delete&id=${encodeURIComponent(this.store.doc.id)}`;
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`Failed to delete template, HTTP status = ${response.status}`);
            }
            await response.json();

            let newTemplateId = this.selectNewTemplateId(this.store.doc.id);
            setCurrentTemplate(this.store, newTemplateId);
            delete this.store.templatesMap[this.store.doc.id];
            this.msgPopup.success('Template successfully deleted and changed.');
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        }
    }

    importTemplate() {
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
                        this.store.setTemplate(jsonObj);
                        await this.saveTemplate(true);
                        //setCurrentTemplate(this.store, newTemplateId);
                        this.store.getTemplates(this.msgPopup);
                        this.msgPopup.success('Template imported and saved successfully.');
                    } catch (err) {
                        this.msgPopup.error('Failed to import template: ' + err, {
                            closable: true,
                            duration: this.errorTimeout
                        });
                    }
                };
                reader.readAsText(file);
            }
        };
        fileInput.click();
    }

    exportTemplate() {
        const filename = `${this.store.doc.name || 'template'}.json`;
        const jsonStr = JSON.stringify(this.store.doc, (key, value) => {
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


    selectNewTemplateId(currentTemplateId) {
        let keys = Object.keys(this.store.templatesMap);
        if (keys.length === 0) {
            return null;
        }
        let currentIndex = keys.indexOf(currentTemplateId);
        let nextIndex = (currentIndex + 1) % keys.length;
        return keys[nextIndex] || keys[0];
    }
}

export default TemplateManager;
