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
            wrapper: ''
        }
    }),
    actions: {
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
                this.doc.maxArticles = data.maxArticles;
                this.doc.maxArticlesShort = data.maxArticlesShort;
                this.doc.html = data.content;
                this.doc.banner = data.banner;
                this.doc.wrapper = data.wrapper;
            } catch (error) {
                message.error(error.message);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
