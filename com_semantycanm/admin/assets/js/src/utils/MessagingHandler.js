import { ref } from 'vue';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';

export class MessagingHandler {
    constructor(newsLetterStore) {
        this.newsLetterStore = newsLetterStore;
        this.isTestMessage = ref(false);
    }

    async send(params) {
        const newsletterApiManager = new NewsletterApiManager();

        if (params.onlySave) {
            try {
                const newsletterApiManager = new NewsletterApiManager();
                console.log(params);
                return await newsletterApiManager.upsert(params, params.id);
            } catch (error) {
                throw error;
            }
        } else {
            let listItems;
            if (params.isTestMessage) {
                listItems = [params.testEmail.trim()];
            } else {
                listItems = params.mailingList.map(item => item.value);
            }

            newsletterApiManager.sendEmail(params.subject, params.messageContent, params.mailingList)
                .then((response) => {
                    console.log('response data:', response.data);
                    this.newsLetterStore.currentNewsletterId = response.data;
                })
                .catch(error => {
                    throw error;
                });
        }
    }

    async fetchSubject() {
        try {
            return await UserExperienceHelper.getSubject();
        } catch (error) {
            throw new Error("Failed to fetch subject");
        }
    }

    //deprected ?
    async handleSendNewsletter(modelRef, formRef, loadingBar, msgPopup, router, onlySave, newsletterId) {
        formRef.value.validate((errors) => {
            if (!errors) {
                loadingBar.start();
                const { templateId, subject, content, useWrapper, customFields, isTestMessage, mailingListIds, testEmail } = modelRef;

                this.send(subject, content, useWrapper, templateId, customFields, isTestMessage, mailingListIds, testEmail, onlySave, newsletterId)
                    .then(() => {
                        if (onlySave) {
                            msgPopup.success('Newsletter saved successfully', {
                                closable: true,
                                duration: 5000
                            });
                        } else {
                            msgPopup.success('Newsletter sent successfully', {
                                closable: true,
                                duration: 5000
                            });
                        }
                        router.push('/list');  // Navigate back to the list after saving/sending
                    })
                    .catch(error => {
                        console.error('Error sending/saving newsletter:', error);
                        msgPopup.error('An error occurred while sending/saving the newsletter', {
                            closable: true,
                            duration: 5000
                        });
                    })
                    .finally(() => {
                        loadingBar.finish();
                    });
            } else {
                if (errors && typeof errors === 'object') {
                    const errorMessages = {};

                    Object.keys(errors).forEach(fieldName => {
                        const fieldError = errors[fieldName];
                        if (fieldError && fieldError.length > 0) {
                            errorMessages[fieldName] = fieldError[0].message + ` [${fieldName}]`;
                        }
                    });

                    Object.values(errorMessages).forEach(errorMessage => {
                        msgPopup.error(errorMessage, {
                            closable: true,
                            duration: 5000
                        });
                    });
                }
            }
        });
    }
}
