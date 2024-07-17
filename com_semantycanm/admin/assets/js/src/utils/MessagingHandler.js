import { ref } from 'vue';
import NewsletterApiManager from "../stores/newsletter/NewsletterApiManager";
import UserExperienceHelper from './UserExperienceHelper';
import { useMessage } from 'naive-ui';
import { NewsletterParams } from './NewsletterParams';

export class MessagingHandler {
    constructor() {
        this.newsletterApiManager = new NewsletterApiManager();
        this.msgPopup = useMessage();
    }

    async send(params) {
        if (params.onlySave) {
            return this.newsletterApiManager.upsert(params);
        }
        return this.newsletterApiManager.sendEmail(params);
    }

    async fetchSubject() {
        try {
            return await UserExperienceHelper.getSubject();
        } catch (error) {
            this.handleError(new Error("Failed to fetch subject"));
        }
    }

    async handleSendAndSave(modelRef, formRef, loadingBar, router, onlySave, newsletterId) {
        try {
            if (!await this.validateForm(formRef)) {
                return;
            }

            loadingBar.start();
            const newsletterParams = new NewsletterParams(modelRef, modelRef.value.content, onlySave, newsletterId.value);
            const response = await this.send(newsletterParams);

            this.handleSuccess(response, onlySave);
            router.push('/list');
        } catch (error) {
            this.handleError(error);
        } finally {
            loadingBar.finish();
        }
    }

    async validateForm(formRef) {
        return new Promise((resolve) => {
            formRef.value.validate((errors) => {
                if (!errors) {
                    resolve(true);
                    return;
                }

                this.displayFormErrors(errors);
                resolve(false);
            });
        });
    }

    handleSuccess(response, onlySave) {
        const message = onlySave ? 'Newsletter saved successfully' : 'Newsletter sent successfully';
        this.msgPopup.success(message, { closable: true, duration: 5000 });
    }

    handleError(error) {
        console.error('Error:', error);
        const errorMessage = error.message || 'An unexpected error occurred';
        this.msgPopup.error(errorMessage, { closable: true, duration: 5000 });
    }

    displayFormErrors(errors) {
        Object.entries(errors).forEach(([fieldName, fieldErrors]) => {
            if (!fieldErrors || fieldErrors.length === 0) {
                return;
            }

            const errorMessage = `${fieldErrors[0].message} [${fieldName}]`;
            this.msgPopup.error(errorMessage, { closable: true, duration: 5000 });
        });
    }
}