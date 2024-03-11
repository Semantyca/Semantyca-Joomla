import {defineStore} from 'pinia';

export const useArticleStore = defineStore('article', {
    state: () => ({
        listPage: {
            docs: []
        },
        selectedArticles: [],
        editorCont: '',
        isLoading: false,
    }),
    actions: {
        async fetchArticles(searchTerm) {
            this.isLoading = true;

            try {
                const url = `index.php?option=com_semantycanm&task=Article.search&q=${encodeURIComponent(searchTerm)}`;
                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error(`Network response was not ok for searchTerm ${searchTerm}`);
                }

                const data = await response.json();
                this.listPage.docs = data.data.map(a => ({
                    id: a.id,
                    title: a.title,
                    url: a.url,
                    category: a.category,
                    intro: encodeURIComponent(a.introtext)
                }));

            } catch (error) {
                console.error(`Problem fetching articles:`, error);
            } finally {
                this.isLoading = false;
            }
        }
    }
});
