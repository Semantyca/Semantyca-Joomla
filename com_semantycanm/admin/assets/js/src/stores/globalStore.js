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
        }
    }),
    actions: {
        async fetchStatisticsData(page) {
            try {
                const response = await fetch('index.php?option=com_semantycanm&task=Stat.findAll&page=' + page + '&limit=10');
                const data = await response.json();
                if (data.success && data.data) {
                    this.statisticView.totalStatList = data.data.count;
                    this.statisticView.currentStatList = data.data.current;
                    this.statisticView.maxStatList = data.data.maxPage;
                    this.statisticView.docs = data.data.docs;
                }
            } catch (error) {
                showAlertBar("Error: " + error);
            } finally {
                hideSpinner('statSpinner');
            }
        }
    }
});
