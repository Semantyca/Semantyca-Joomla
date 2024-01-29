import {defineStore} from 'pinia';

export const useNewsletterStore = defineStore('newsletter', {
    state: () => ({
        messageContent: '',
        newsLetterDocsView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        }
    }),
    actions: {
        async fetchNewsletters(page) {
            //   showSpinner('newsletterSpinner');
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=NewsLetter.findAll&page=' + page + '&limit=10');

                if (!response.ok) {
                    throw new Error(`HTTP error, status = ${response.status}`);
                }
                const responseData = await response.json();
                if (responseData.success && responseData.data) {
                    this.newsLetterDocsView.total = responseData.data.count;
                    this.newsLetterDocsView.current = responseData.data.current;
                    this.newsLetterDocsView.maxPage = responseData.data.maxPage;
                    this.newsLetterDocsView.docs = responseData.data.docs;
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
