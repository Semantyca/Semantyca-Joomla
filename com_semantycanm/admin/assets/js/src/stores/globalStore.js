import {defineStore} from 'pinia';

export const useGlobalStore = defineStore('global', {
    state: () => ({
        translations: window.globalTranslations || {},
        tinyMceLic: window.tinymceLic,
        statisticView: {
            totalStatList: 0,
            currentStatList: 1,
            maxStatList: 0,
            docs: []
        },
        mailingList: {
            totalStatList: 0,
            currentStatList: 1,
            maxStatList: 0,
            docs: []
        },
        newsLetterDocsView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        }
    }),
    actions: {
        async fetchStatisticsData(page) {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.findAll&page=' + page + '&limit=10');
                const viewData = await response.json();
                if (viewData.success && viewData.data) {
                    this.statisticView.totalStatList = viewData.data.count;
                    this.statisticView.currentStatList = viewData.data.current;
                    this.statisticView.maxStatList = viewData.data.maxPage;
                    this.statisticView.docs = viewData.data.docs;
                }
            } catch (error) {
                showAlertBar("Error: " + error);
            } finally {
                hideSpinner('statSpinner');
            }
        },
        async getPageOfMailingList(page) {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=MailingList.findall&page=' + page + '&limit=10');
                const viewData = await response.json();
                if (viewData.success && viewData.data) {
                    this.mailingList.totalStatList = viewData.data.count;
                    this.mailingList.currentStatList = viewData.data.current;
                    this.mailingList.maxStatList = viewData.data.maxPage;
                    this.mailingList.docs = viewData.data.docs;
                }
            } catch (error) {
                showErrorBar('MailingList.findall', error);
            }
        },
        async fetchNewsletters(page) {
            showSpinner('newsletterSpinner');
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
                showErrorBar('NewsLetter.findAll', error.message);
            } finally {
                hideSpinner('newsletterSpinner');
            }
        }

    }
});
