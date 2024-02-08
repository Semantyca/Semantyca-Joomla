import {defineStore} from 'pinia';
import {useMailingListStore} from './mailinglistStore.js';

export const useUserGroupStore = defineStore('userGroup', {
    state: () => ({
        documentsPage: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        },
        lastFetch: 0,
    }),
    getters: {
        isDataStale: (state) => {
            const tenMinutes = 2 * 60 * 1000;
            return Date.now() - state.lastFetch > tenMinutes;
        }
    },
    actions: {
        async getList() {
            if (this.documentsPage.docs.length === 0 || this.isDataStale) {
                try {
                    const url = `index.php?option=com_semantycanm&task=UserGroup.find`;
                    const response = await fetch(url);
                    if (!response.ok) {
                        throw new Error(`Network response was not 200 for name ${name}`);
                    }
                    const json = await response.json();
                    this.documentsPage.total = json.data.count;
                    this.documentsPage.current = json.data.current;
                    this.documentsPage.maxPage = json.data.maxPage;
                    this.documentsPage.docs = json.data.docs;
                    this.lastFetch = Date.now();
                } catch (error) {
                    console.log(error);
                    //  showErrorBar('UserGroup.find', error.message);
                }
            }
        },
        async saveList(model, mode) {
            try {
                const mailingListName = model.groupName;
                const listItems = model.selectedGroups.map(group => group.id);
                const request = new MailingListRequest(mode);
                startLoading('loadingSpinner');
                request.process(
                    mailingListName,
                    listItems,
                    async (data) => {
                        await useMailingListStore().refreshMailingList(1);
                    }
                );
            } catch (error) {
                console.log(error);
            } finally {
                stopLoading('loadingSpinner');
            }
        }
    }
});
