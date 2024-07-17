export class NewsletterParams {
    constructor(modelRef, content, onlySave, id) {
        const {
            templateId, subject, useWrapper, customFields,
            isTestMessage, mailingListIds, testEmail
        } = modelRef.value;

        this.templateId = templateId;
        this.subject = subject;
        this.content = content;
        this.useWrapper = useWrapper;
        this.customFields = customFields;
        this.isTestMessage = isTestMessage;
        this.mailingListIds = mailingListIds;
        this.testEmail = testEmail;
        this.onlySave = onlySave;
        this.id = id;
    }
}