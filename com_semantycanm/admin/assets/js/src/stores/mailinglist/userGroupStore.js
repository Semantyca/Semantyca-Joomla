import {defineStore} from 'pinia';

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
            const cacheTime = 5 * 60 * 1000;
            return Date.now() - state.lastFetch > cacheTime;
        }
    },
    actions: {
        async getList(msgPopup, loadingBar) {
            if (!this.isDataStale && this.documentsPage.docs.length > 0) {
                return;
            }
            loadingBar.start();
            try {
                const url = `index.php?option=com_semantycanm&task=UserGroup.find`;
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Network response was not 200`);
                }
                const json = await response.json();
                this.documentsPage.total = json.data.count;
                this.documentsPage.current = json.data.current;
                this.documentsPage.maxPage = json.data.maxPage;
                this.documentsPage.docs = json.data.docs;
                this.lastFetch = Date.now();
            } catch (error) {
                loadingBar.error();
                msgPopup.error(error.message, {
                    closable: true,
                    duration: this.$errorTimeout
                });
            } finally {
                loadingBar.finish();
            }
        }
    }
});
