import {defineStore} from 'pinia';

export const useTemplateStore = defineStore('template', {
    state: () => ({
        doc: {
            id: 0,
            name: '',
            maxArticles: '',
            maxArticlesShort: '',
            html: '',
            banner: '',
            wrapper: '',
            customFields: []
        }
    }),
    actions: {
        addCustomField(newField) {
            this.doc.customFields.push(newField);
        },
        async getTemplate(name, message) {
            try {
                startLoading('loadingSpinner');
                const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Network response was not ok for name ${name}`);
                }
                const {data} = await response.json();
                this.doc.id = data.id;
                this.doc.name = data.name;
                this.doc.html = data.content;
                this.doc.wrapper = data.wrapper;
                this.doc.customFields = data.customFields;
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async saveTemplate(templateId, htmlContent, onSuccess, onError) {
            const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(templateId)}`;
            const data = {
                html: htmlContent
            };

            try {
                const response = await fetch(endpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(data)
                });

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }

                console.log('Template saved successfully');
                onSuccess?.();
            } catch (error) {
                onError?.(error);
            }
        }
    }
});
