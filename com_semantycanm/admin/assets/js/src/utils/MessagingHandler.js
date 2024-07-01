import { ref } from 'vue';
import { useMessage, useLoadingBar } from 'naive-ui';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';

export class MessagingHandler {

    constructor(formRef, modelRef, newsLetterStore) {
        this.formRef = formRef;
        this.modelRef = modelRef;
        this.newsLetterStore = newsLetterStore;
        this.msgPopup = useMessage();
        this.loadingBar = useLoadingBar();
        this.isTestMessage = ref(false);
        this.testUserEmail = ref('');
    }

    async send(isTestMessage, testUserEmail, onlySave = false) {
        this.formRef.value.validate((errors) => {
            if (!errors) {
                const subj = this.modelRef.subject;
                const msgContent = this.modelRef.content;
                const newsletterApiManager = new NewsletterApiManager(this.msgPopup, this.loadingBar);

                if (onlySave) {
                    newsletterApiManager.saveNewsletter(subj, msgContent);
                } else {
                    let listItems;
                    if (isTestMessage) {
                        listItems = [testUserEmail.trim()];
                    } else {
                        listItems = this.modelRef.mailingList.map(item => item.value);
                    }

                    newsletterApiManager.sendEmail(subj, msgContent, listItems)
                        .then((response) => {
                            console.log('response data:', response.data);
                            this.newsLetterStore.currentNewsletterId = response.data;
                            this.newsLetterStore.startPolling();
                        })
                        .catch(error => {
                            console.log('error: ', error.message);
                            this.msgPopup.error(error.message, {
                                closable: true,
                                duration: 10000
                            });
                        });
                }
            } else {
                Object.keys(errors).forEach(fieldName => {
                    const fieldError = errors[fieldName];
                    if (fieldError && fieldError.length > 0) {
                        this.msgPopup.error(fieldError[0].message, {
                            closable: true,
                            duration: 10000
                        });
                    }
                });
            }
        });
    }

    async fetchSubject() {
        try {
            return await UserExperienceHelper.getSubject(this.loadingBar);
        } catch (error) {
            throw new Error("Failed to fetch subject");
        }
    }
}