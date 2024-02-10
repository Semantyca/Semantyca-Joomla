import {defineStore} from 'pinia';

export const useNewsletterStore = defineStore('newsletter', {
    state: () => ({
        docsListPage: {
            docs: []
        }
    }),
    actions: {
        async fetchNewsLetter(page, size, pagination) {
            //   showSpinner('newsletterSpinner');
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=NewsLetter.findAll&page=' + page + '&limit=' + size);

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const respData = await response.json();
                if (respData.success && respData.data) {
                    this.docsListPage.docs = respData.data.docs;
                    pagination.pageSize = size;
                    pagination.itemCount = respData.data.count;
                    pagination.pageCount = respData.data.maxPage;
                    pagination.page = respData.data.current;
                }
            } catch (error) {
                console.error(error);
                //  showErrorBar('NewsLetter.findAll', error.message);
            } finally {
                //  hideSpinner('newsletterSpinner');
            }
        }
    }
});
