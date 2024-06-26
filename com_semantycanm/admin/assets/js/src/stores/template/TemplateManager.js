import {setCurrentTemplate} from "../storeUtils";
import BaseObject from "../../utils/BaseObject";

class TemplateManager extends BaseObject {

    constructor(store, msgPopup, loadingBar) {
        super();
        this.templateStore = store;
        this.msgPopup = msgPopup;
        this.loadingBar = loadingBar;
        this.errorTimeout = 50000;
    }

    async getTemplates() {
        const currentPage = 1;
        const itemsPerPage = 10;
        this.loadingBar.start();
        const url = `index.php?option=com_semantycanm&task=Template.findAll&page=${currentPage}&limit=${itemsPerPage}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Failed to fetch templates, HTTP status = ${response.status}`);
            }
            const jsonResponse = await response.json();
            if (jsonResponse.success && jsonResponse.data) {
                this.templateStore.templateMap = jsonResponse.data.templates.reduce((acc, template) => {
                    acc[template.id] = template;
                    return acc;
                }, {});

                this.templateStore.pagination = {
                    currentPage: jsonResponse.data.current,
                    itemsPerPage: itemsPerPage,
                    totalItems: jsonResponse.data.count,
                    totalPages: jsonResponse.data.maxPage
                };
                const defaultTemplateId = Object.keys(this.templateStore.templateMap).find(id => this.templateStore.templateMap[id].isDefault);
                if (defaultTemplateId) {
                    setCurrentTemplate(this.templateStore, defaultTemplateId);
                }
            } else {
                throw new Error('Failed to fetch templates: No data returned');
            }
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error('Error fetching templates: ' + error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.loadingBar.finish();
        }
    }

    async saveTemplate(doc, isNew = false) {
        this.startBusyMessage('Saving template ...');
        const endpoint = isNew ? `index.php?option=com_semantycanm&task=Template.update&id=` :
            `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(doc.id)}`;
        const method = 'POST';

        try {
            const response = await fetch(endpoint, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(doc)
            });

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            this.msgPopup.success(result.data.message);

            await this.getTemplates();
            return result;
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage();
        }
    }

    async deleteCurrentTemplate() {
        this.startBusyMessage(`Deleting "${this.templateStore.currentTemplate.name}" template ...`);
        const endpoint = `index.php?option=com_semantycanm&task=Template.delete&id=${encodeURIComponent(this.templateStore.currentTemplate.id)}`;
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`Failed to delete template, HTTP status = ${response.status}`);
            }
            await response.json();
            const deletedId = this.templateStore.currentTemplate.id;
            console.log('deletedId', deletedId);

            await this.getTemplates();

            const newTemplateId = this.selectNewTemplateId(deletedId);
            console.log('new', newTemplateId);
            this.handleTemplateChange(newTemplateId);

            this.msgPopup.success('Template successfully deleted');
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage();
        }
    }

    async setDefaultTemplate(templateId) {
        const endpoint = `index.php?option=com_semantycanm&task=Template.setDefault&id=${encodeURIComponent(templateId)}`;
        const method = 'POST';

        try {
            const response = await fetch(endpoint, {
                method,
                headers: { 'Content-Type': 'application/json' }
            });

            if (!response.ok) {
                throw new Error(`Failed to set default template, HTTP status = ${response.status}`);
            }
            const result = await response.json();
            if (!result.success) {
                throw new Error(result.message || 'Failed to set default template.');
            }
        } catch (error) {
            this.msgPopup.error('Error setting default template: ' + error.message, {
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
                        this.startBusyMessage('Importing template ...');
                        const jsonObj = JSON.parse(e.target.result);
                        await this.saveTemplate(jsonObj, true);
                        await this.getTemplates();
                        this.msgPopup.success('Template imported and saved successfully.');
                    } catch (err) {
                        this.msgPopup.error('Failed to import template: ' + err, {
                            closable: true,
                            duration: this.errorTimeout
                        });
                    } finally {
                        this.stopBusyMessage();
                    }
                };
                reader.readAsText(file);
            }
        };
        fileInput.click();
    }

    exportCurrentTemplate() {
        const filename = `${this.templateStore.currentTemplate.name || 'template'}.json`;
        const jsonStr = JSON.stringify(this.templateStore.currentTemplate, (key, value) => {
            if (key === "id" || key === "availableCustomFields" || key === "regDate") return undefined;
            return value;
        }, 2);
        const blob = new Blob([jsonStr], { type: "application/json" });
        const url = URL.createObjectURL(blob);
        const link = document.createElement('a');
        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    async handleTemplateChange(newTemplateId) {
        this.templateStore.setCurrentTemplate(this.templateStore.templateMap[newTemplateId]);
        this.setDefaultTemplate(newTemplateId);
    }

    selectNewTemplateId() {
        const keys = Object.keys(this.templateStore.templateMap);
        if (keys.length === 0) {
            return null;
        }

        const numericKeys = keys.map(Number);
        const minKey = Math.min(...numericKeys);

        if (minKey === Infinity) {
            const maxKey = Math.max(...numericKeys);
            return maxKey.toString();
        }
        return minKey.toString();
    }
}

export default TemplateManager;