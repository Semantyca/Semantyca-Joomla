import {defineStore} from 'pinia';
import {useComposerStore} from './composerStore';

export const useTemplateStore = defineStore('template', {
    state: () => ({
        doc: {
            id: 0,
            name: '',
            html: '',
            wrapper: '',
            customFields: []
        }
    }),
    actions: {
        addCustomField(newField) {
            const defaultFieldStructure = {
                name: '',
                type: '',
                caption: '',
                defaultValue: '',
                isAvailable: 0,
            };
            this.doc.customFields.push({...defaultFieldStructure, ...newField});
        },
        removeCustomField(index) {
            if (index >= 0 && index < this.doc.customFields.length) {
                this.doc.customFields.splice(index, 1);
            }
        },
        setTemplate(data) {
            // Explicitly update `doc` properties to ensure detailed control over what gets updated
            this.doc.id = data.id ?? this.doc.id; // Use nullish coalescing to fallback to current values if undefined
            this.doc.name = data.name ?? this.doc.name;
            this.doc.html = data.content ?? this.doc.html; // Assuming `content` from `data` matches `html` in `doc`
            this.doc.wrapper = data.wrapper ?? this.doc.wrapper;
            this.doc.customFields = data.customFields ?? this.doc.customFields;

            // If there's additional logic needed to process `customFields` or other properties, add here
            // For example, if you need to ensure `defaultValue` for each customField is correctly formatted
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
        async saveTemplate(message) {
            startLoading('loadingSpinner');
            const endpoint = `index.php?option=com_semantycanm&task=Template.update&id=${encodeURIComponent(this.doc.id)}`;
            const data = {
                doc: this.doc
            };

            for (let field of this.doc.customFields) {
                if (field.type === 504 || field.type === 503) {

                    try {
                        if (Array.isArray(field.defaultValue)) {
                            // field.defaultValue = JSON.stringify(field.defaultValue);
                        } else {
                            // field.defaultValue = JSON.stringify(field.defaultValue);
                            // message.error(`Invalid input in field "${field.caption}".`);
                        }
                    } catch (error) {
                        // Handle any unexpected error during the JSON conversion
                        message.error(`Unexpected error in field "${field.caption}": ${error}`);
                        stopLoading('loadingSpinner');
                        return; // Abort the save operation
                    }
                }
            }

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
                message.success('Template saved successfully');
                const composerStore = useComposerStore();
                await composerStore.updateFormCustomFields(message);
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
