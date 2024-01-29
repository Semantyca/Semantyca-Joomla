import {defineStore} from 'pinia';

export const useUserGroupStore = defineStore('userGroup', {
    state: () => ({
        documentsPage: {
            total: 0,
            current: 1,
            maxPage: 0,
            docs: []
        }
    }),
    actions: {
        async getList() {
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
            } catch (error) {
                console.log(error);
                //  showErrorBar('UserGroup.find', error.message);
            }
        }
    }
});
