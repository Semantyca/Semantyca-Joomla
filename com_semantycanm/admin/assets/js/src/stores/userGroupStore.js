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
        },
        async saveList(state) {
            startLoading('loadingSpinner');
            console.log('Mailing List Name:', state.groupName);
            const mailingListName = state.groupName;
            const listItems = state.selectedGroups.map(group => group.id);
            if (mailingListName === '') {
                //   showWarnBar("Mailing list name cannot be empty");
            } else if (listItems.length === 0) {
                //     showWarnBar('The list is empty');
            } else {
                let mode = document.getElementById('mailingListMode').value;
                const mailingListRequest = new MailingListRequest(mode);
                mailingListRequest.process(mailingListName, listItems);
            }
            stopLoading('loadingSpinner');
        }
    }
});
