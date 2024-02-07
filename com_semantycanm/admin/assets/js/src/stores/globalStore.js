import {defineStore} from 'pinia';

export const useGlobalStore = defineStore('global', {
    state: () => ({
        translations: window.globalTranslations || {},
        tinyMceLic: window.tinymceLic,
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
        }
    }),
    actions: {
        async getTemplate(name) {
            try {
                startLoading('loadingSpinner');
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
            } finally {
                stopLoading('loadingSpinner');
            }
        },
        async fetchStatisticsData(page) {
            try {
                startLoading('loadingSpinner');
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
                stopLoading('loadingSpinner');
            }
        }
    }
});
