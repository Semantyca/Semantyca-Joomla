import {defineStore} from 'pinia';

export const useGlobalStore = defineStore('global', {
    state: () => ({
        translations: window.globalTranslations || {},
        tinyMceLic: window.tinymceLic,
        userGroupDocsView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        },
        mailingListDocsView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        },
        template: {
            id: 0,
            name: '',
            maxArticles: '',
            maxArticlesShort: '',
            html: '',
            banner: '',
            wrapper: ''
        },
        statisticView: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        },
        mailingList: {
            totalStatList: 0,
            currentStatList: 1,
            maxStatList: 0,
            docs: []
        }
    }),
    actions: {
        async refreshMailingList(currentPage) {
            //showSpinner('listSpinner');

            const url = 'index.php?option=com_semantycanm&task=MailingList.findall&page=' + currentPage + '&limit=10';

            fetch(url, {
                method: 'GET',
            })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok: ' + response.statusText);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.data) {
                        this.mailingListDocsView.total = data.data.count;
                        this.mailingListDocsView.current = data.data.current;
                        this.mailingListDocsView.maxPage = data.data.maxPage;
                        this.mailingListDocsView.docs = data.data.docs;
                    }
                })
                .catch(error => {
                    showErrorBar('MailingList.findall', error.message);
                })
                .finally(() => {
                    //hideSpinner('listSpinner');
                });
        },
        async getTemplate(name) {
            try {
                const url = `index.php?option=com_semantycanm&task=Template.find&name=${name}`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Network response was not 200 for name ${name}`);
                }
                const {data} = await response.json();
                this.template.id = data.id;
                this.template.name = data.name;
                this.template.maxArticles = data.maxArticles;
                this.template.maxArticlesShort = data.maxArticlesShort;
                this.template.html = data.content;
                this.template.banner = data.banner;
                this.template.wrapper = data.wrapper;
            } catch (error) {
                showErrorBar('Template.find&name', error.message);
            }
        },
        async fetchStatisticsData(page) {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.findAll&page=' + page + '&limit=10');
                const viewData = await response.json();
                if (viewData.success && viewData.data) {
                    this.statisticView.total = viewData.data.count;
                    this.statisticView.current = viewData.data.current;
                    this.statisticView.maxPage = viewData.data.maxPage;
                    this.statisticView.docs = viewData.data.docs;
                }
            } catch (error) {
                showAlertBar("Error: " + error);
            } finally {
                // hideSpinner('statSpinner');
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
        }
    }
});
