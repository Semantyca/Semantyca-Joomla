import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager"
import UserExperienceHelper from "./UserExperienceHelper"

export const sendNewsletter = async (modelRef, isTestMessage, testUserEmail, formRef, msgPopup, loadingBar, newsLetterStore, onlySave = false) => {
    formRef.value.validate((errors) => {
        if (!errors) {
            const subj = modelRef.subject;
            const msgContent = modelRef.content;
            const newsletterApiManager = new NewsletterApiManager(msgPopup, loadingBar);
            if (onlySave) {
                newsletterApiManager.saveNewsletter(subj, msgContent);
            } else {
                let listItems;
                if (isTestMessage) {
                    listItems = [testUserEmail.trim()];
                } else {
                    listItems = modelRef.mailingList.map(item => item.value);
                }
                newsletterApiManager.sendEmail(subj, msgContent, listItems)
                    .then((response) => {
                        console.log('response data:', response.data);
                        newsLetterStore.currentNewsletterId = response.data;
                        newsLetterStore.startPolling();
                    })
                    .catch(error => {
                        console.log('error: ', error.message);
                        msgPopup.error(error.message, {
                            closable: true,
                            duration: 10000
                        });
                    });
            }
        } else {
            Object.keys(errors).forEach(fieldName => {
                const fieldError = errors[fieldName];
                if (fieldError && fieldError.length > 0) {
                    msgPopup.error(fieldError[0].message, {
                        closable: true,
                        duration: 10000
                    });
                }
            });
        }
    });
};

export const fetchSubject = async (loadingBar) => {
    try {
        return await UserExperienceHelper.getSubject(loadingBar);
    } catch (error) {
        throw new Error("Failed to fetch subject");
    }
};