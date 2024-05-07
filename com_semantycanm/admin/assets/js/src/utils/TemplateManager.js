import {setCurrentTemplate} from "../stores/utils/fieldUtilities";

class TemplateManager {
    messageReactive = null;

    constructor(store, msgPopup) {
        this.store = store;
        this.msgPopup = msgPopup;
        this.errorTimeout = 50000;
    }

    async getTemplates(msgPopup, currentPage = 1, itemsPerPage = 10) {
        this.startBusyMessage('Fetching templates ...')
        const url = `index.php?option=com_semantycanm&task=Template.findAll&page=${currentPage}&limit=${itemsPerPage}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Failed to fetch templates, HTTP status = ${response.status}`);
            }
            const jsonResponse = await response.json();
            if (jsonResponse.success && jsonResponse.data) {
                this.store.templateMap = jsonResponse.data.templates.reduce((acc, template) => {
                    acc[template.id] = template;
                    return acc;
                }, {});
                this.store.pagination = {
                    currentPage: jsonResponse.data.current,
                    itemsPerPage: itemsPerPage,
                    totalItems: jsonResponse.data.count,
                    totalPages: jsonResponse.data.maxPage
                };
                const defaultTemplateId = Object.keys(this.store.templateMap).find(id => this.store.templateMap[id].isDefault);
                if (defaultTemplateId) {
                    setCurrentTemplate(this.store, defaultTemplateId);
                }
            } else {
                throw new Error('Failed to fetch templates: No data returned');
            }
        } catch (error) {
            msgPopup.error('Error fetching templates: ' + error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage()
        }
    }

    async autoSave(field, dataToUpdate) {
        this.startBusyMessage('Saving ...')
        const endpoint = `index.php?option=com_semantycanm&task=Template.autoSave&id=${encodeURIComponent(this.store.doc.id)}`;
        const method = 'PUT';
        const payload = {
            [field]: dataToUpdate
        };

        try {
            const response = await fetch(endpoint, {
                method: method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            });

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }

            const jsonResponse = await response.json();
            if (!jsonResponse.success) {
                throw new Error(jsonResponse.message || "Failed to auto-save the template.");
            }
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage()
        }
    }

    async saveTemplate(doc, isNew = false) {
        this.startBusyMessage('Saving template ...')
        const endpoint = isNew ? `index.php?option=com_semantycanm&task=Template.update&id=` :
            `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(doc.id)}`;
        const method = 'POST';

        try {
            const response = await fetch(endpoint, {
                method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(doc)
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
        } finally {
            this.stopBusyMessage()
        }
    }

    async deleteCurrentTemplate() {
        this.startBusyMessage('Deleting template ...')
        const endpoint = `index.php?option=com_semantycanm&task=Template.delete&id=${encodeURIComponent(this.store.doc.id)}`;
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE'
            });

            if (!response.ok) {
                throw new Error(`Failed to delete template, HTTP status = ${response.status}`);
            }
            await response.json();
            const deletedId = this.store.doc.id;
            console.log('deletedId', deletedId);
            const newTemplateId = this.selectNewTemplateId(deletedId);
            console.log('new', newTemplateId);
            this.handleTemplateChange(newTemplateId);
            delete this.store.templateMap[deletedId];
            this.msgPopup.success('Template successfully deleted');
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage()
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
                        this.startBusyMessage('Importing template ...')
                        const jsonObj = JSON.parse(e.target.result);
                        await this.saveTemplate(jsonObj, true);
                        await this.store.getTemplates(this.msgPopup);
                        this.msgPopup.success('Template imported and saved successfully.');
                    } catch (err) {
                        this.msgPopup.error('Failed to import template: ' + err, {
                            closable: true,
                            duration: this.errorTimeout
                        });
                    } finally {
                        this.stopBusyMessage()
                    }
                };
                reader.readAsText(file);
            }
        };
        fileInput.click();
    }

    exportCurrentTemplate() {
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

    handleTemplateChange(newTemplateId) {
        setCurrentTemplate(this.store, newTemplateId);
    };

    selectNewTemplateId(currentTemplateId) {
        let keys = Object.keys(this.store.templateMap);
        if (keys.length === 0) {
            return null;
        }
        let currentIndex = keys.indexOf(currentTemplateId);
        let nextIndex = (currentIndex + 1) % keys.length;
        return keys[nextIndex] || keys[0];
    }

    startBusyMessage(msg) {
        if (!this.messageReactive) {
            this.messageReactive = this.msgPopup.loading(msg, {
                duration: 0
            });
        }
    }

    stopBusyMessage() {
        if (this.messageReactive) {
            this.messageReactive.destroy();
            this.messageReactive = null;
        }
    };
}

export default TemplateManager;
