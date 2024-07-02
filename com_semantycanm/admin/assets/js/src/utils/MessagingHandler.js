import {ref} from 'vue';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';

export class MessagingHandler {
    constructor(newsLetterStore) {
        this.newsLetterStore = newsLetterStore;
        this.isTestMessage = ref(false);
        this.testUserEmail = ref('');
    }

    async send(subj, msgContent, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail, onlySave = false) {
        const newsletterApiManager = new NewsletterApiManager();

        if (onlySave) {
            await this.saveNewsletter(subj, msgContent, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail);
        } else {
            let listItems;
            if (isTestMessage) {
                listItems = [testEmail.trim()];
            } else {
                listItems = mailingList.map(item => item.value);
            }

            newsletterApiManager.sendEmail(subj, msgContent, listItems)
                .then((response) => {
                    console.log('response data:', response.data);
                    this.newsLetterStore.currentNewsletterId = response.data;
                })
                .catch(error => {
                    console.log('error: ', error.message);
                    throw error;
                });
        }
    }

    async saveNewsletter(subj, msgContent, templateId, customFieldsValues, articlesIds, isTest, mailingList, testEmail) {
        try {
            const newsletterApiManager = new NewsletterApiManager(this.loadingBar);
            const result = await newsletterApiManager.upsert({
                template_id: templateId,
                customFieldsValues: customFieldsValues,
                articlesIds: articlesIds,
                subject: subj,
                isTest: isTest,
                mailingList: mailingList,
                testEmail: testEmail,
                messageContent: msgContent
            });
            console.log('Newsletter saved successfully:', result.id);
        } catch (error) {
            console.error('Error saving newsletter:', error.message);
            throw error;
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