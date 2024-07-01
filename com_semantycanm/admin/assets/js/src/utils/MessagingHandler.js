import {ref} from 'vue';
import {useMessage, useLoadingBar} from 'naive-ui';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';

export class MessagingHandler {

    constructor(modelRef, newsLetterStore) {
        this.modelRef = modelRef;
        this.newsLetterStore = newsLetterStore;
        this.msgPopup = useMessage();
        this.loadingBar = useLoadingBar();
        this.isTestMessage = ref(false);
        this.testUserEmail = ref('');
    }

    async send(onlySave = false) {
        const subj = this.modelRef.subject;
        const msgContent = this.modelRef.content;
        const newsletterApiManager = new NewsletterApiManager(this.msgPopup, this.loadingBar);
        console.log(onlySave);

        if (onlySave) {
            await newsletterApiManager.saveNewsletter(subj, msgContent);
        } else {
            let listItems;
            if (this.modelRef.isTestMessage) {
                listItems = [this.modelRef.testEmail.trim()];
            } else {
                listItems = this.modelRef.mailingList.map(item => item.value);
            }

            newsletterApiManager.sendEmail(subj, msgContent, listItems)
                .then((response) => {
                    console.log('response data:', response.data);
                    this.newsLetterStore.currentNewsletterId = response.data;
                })
                .catch(error => {
                    console.log('error: ', error.message);
                    this.msgPopup.error(error.message, {
                        closable: true,
                        duration: 10000
                    });
                });
        }
    }

    async fetchSubject() {
        try {
            return await UserExperienceHelper.getSubject(this.loadingBar);
        } catch (error) {
            throw new Error("Failed to fetch subject");
        }
    }
}