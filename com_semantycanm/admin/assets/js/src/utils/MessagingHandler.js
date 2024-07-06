import {ref} from 'vue';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';

export class MessagingHandler {
    constructor(newsLetterStore) {
        this.newsLetterStore = newsLetterStore;
        this.isTestMessage = ref(false);
    }

    async send(subj, msgContent, useWrapper, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail, onlySave = false, id = null) {
        const newsletterApiManager = new NewsletterApiManager();

        if (onlySave) {
            await this.saveNewsletter(subj, msgContent, useWrapper, templateId, customFieldsValues, selectedArticleIds, isTestMessage, mailingList, testEmail, id);
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
                    throw error;
                });
        }
    }

    async saveNewsletter(subj, msgContent, useWrapper, templateId, customFieldsValues, articlesIds, isTest, mailingList, testEmail, id = null) {
        try {
            const newsletterApiManager = new NewsletterApiManager();
            return await newsletterApiManager.upsert({
                templateId: templateId,
                customFieldsValues: customFieldsValues,
                articlesIds: articlesIds,
                subject: subj,
                isTest: isTest,
                mailingList: mailingList,
                testEmail: testEmail,
                messageContent: msgContent,
                useWrapper: useWrapper
            }, id);
        } catch (error) {
            throw error;
        }
    }

    async fetchSubject() {
        try {
            return await UserExperienceHelper.getSubject();
        } catch (error) {
            throw new Error("Failed to fetch subject");
        }
    }
}