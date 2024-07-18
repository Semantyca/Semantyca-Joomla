import UserExperienceHelper from './UserExperienceHelper';
import {useMessage} from 'naive-ui';
import {NewsletterParams} from './NewsletterParams';

const BASE_URL = 'index.php?option=com_semantycanm&task=newsletters';
const MAIL_SERVICE_URL = 'index.php?option=com_semantycanm&task=service';
const headers = new Headers({
    'Content-Type': 'application/json',
});

export class MessagingHandler {
    constructor() {
        this.msgPopup = useMessage();
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
            if (!response.ok) {
                await this.handleSoftError(response, onlySave);
            } else {
                this.handleSuccess(response, onlySave);
                router.push('/list');
            }
        } catch (error) {
            console.log('error: ',error);
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

    async send(params) {
        let url;
        if (params.onlySave) {
            url = `${BASE_URL}.upsert${params.id ? '&id=' + params.id : ''}`;
        } else {
            url = `${MAIL_SERVICE_URL}.sendEmailAsync${params.id ? '&id=' + params.id : ''}`;
        }
        return fetch(url, {
            method: 'POST',
            headers: headers,
            body: JSON.stringify(params),
        });
    }

    handleSuccess(response, onlySave) {
        const message = onlySave ? 'Newsletter saved successfully' : 'Newsletter sent successfully';
        this.msgPopup.success(message, { closable: true, duration: 5000 });
    }

    async handleSoftError(response, onlySave) {
        const responseData = await response.json();
        console.log('response data: ', responseData);
        if (response.status === 422) {
            const errorMessages = responseData.details.map(error => `${error}`).join('\n');
            this.msgPopup.warning(
                `Validation Error:\n${errorMessages}`,
                { closable: true, duration: 10000 }
            );
        } else {
            const message = onlySave ? 'Newsletter not saved' : 'Newsletter was not sent';
            this.msgPopup.warning(responseData.message || message, { closable: true, duration: 5000 });
        }
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
            this.msgPopup.warning(errorMessage, { closable: true, duration: 5000 });
        });
    }
}