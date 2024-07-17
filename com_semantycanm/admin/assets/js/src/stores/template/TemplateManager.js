import BaseObject from "../../utils/BaseObject";
import {CustomField, Template} from "../../utils/Template";
import {useLoadingBar, useMessage} from "naive-ui";

class TemplateManager extends BaseObject {

    constructor(store) {
        super();
        this.templateStore = store;
        this.msgPopup = useMessage();
        this.loadingBar = useLoadingBar();
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
            if (jsonResponse.success && jsonResponse.data && jsonResponse.data.templates) {
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

    async saveTemplate(doc, id = null) {
        this.loadingBar.start();
        const endpoint = `index.php?option=com_semantycanm&task=Template.upsert&id=${id}`;
        const method = 'POST';

        try {
            const response = await fetch(endpoint, {
                method,
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(doc)
            });

            const result = await response.json();

            if (!response.ok) {
                const errorMessage = result.message || `HTTP error, status = ${response.status}`;
                throw new Error(errorMessage);
            }

            this.msgPopup.success(result.message || 'Template saved successfully');
            return result;
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
            throw error;
        } finally {
            this.loadingBar.finish();
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
                        this.loadingBar.start();
                        const jsonObj = JSON.parse(e.target.result);
                        console.log(jsonObj);

                        const importedTemplate = new Template(
                            0,
                            jsonObj.name,
                            jsonObj.type,
                            jsonObj.description,
                            jsonObj.content,
                            jsonObj.wrapper,
                            jsonObj.isDefault || false,
                            jsonObj.customFields.map(cf => new CustomField(cf))
                        );
                        this.templateStore.setImportedTemplate(importedTemplate);
                        this.msgPopup.success('Template imported successfully');
                    } catch (err) {
                        this.loadingBar.error();
                        this.msgPopup.error('Failed to import template: ' + err, {
                            closable: true,
                            duration: this.errorTimeout
                        });
                    } finally {
                        this.loadingBar.finish();
                    }
                };
                reader.readAsText(file);
            }
        };
        fileInput.click();
    }

    exportCurrentTemplate() {
        const currentTemplate = this.templateStore.templateDoc;
        if (!currentTemplate) {
            this.msgPopup.error('No template selected for export');
            return;
        }

        const filename = `${currentTemplate.name || 'template'}.json`;
        const templateForExport = {
            name: currentTemplate.name,
            type: currentTemplate.type,
            description: currentTemplate.description,
            content: currentTemplate.content,
            wrapper: currentTemplate.wrapper,
            isDefault: currentTemplate.isDefault,
            customFields: currentTemplate.customFields.map(field => ({
                name: field.name,
                type: field.type,
                caption: field.caption,
                defaultValue: field.defaultValue,
                isAvailable: field.isAvailable
            }))
        };

        const jsonStr = JSON.stringify(templateForExport, null, 2);
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


    async deleteTemplates(ids) {
        const idsParam = ids.join(',');
        const endpoint = `index.php?option=com_semantycanm&task=Template.delete&ids=${encodeURIComponent(idsParam)}`;
        try {
            const response = await fetch(endpoint, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error(`Failed to delete templates, HTTP status = ${response.status}`);
            }
            const result = await response.json();
            await this.getTemplates();
            this.msgPopup.success(result.message || 'Templates successfully deleted');
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        }
    }
}

export default TemplateManager;