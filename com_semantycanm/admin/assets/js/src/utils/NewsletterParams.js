export default  class NewsletterParams {
    constructor(modelRef, content,  onlySave = false, id = null ) {
        this.onlySave = onlySave;
        this.id = id;
        this.templateId = modelRef.value.templateId;
        this.customFieldsValues = modelRef.value.customFields;
        this.isTestMessage = modelRef.value.isTestMessage;
        this.testEmail = modelRef.value.testEmail;
        this.mailingList = modelRef.value.mailingListIds;
        this.subject = modelRef.value.subject;
        this.messageContent = content;
        this.useWrapper = modelRef.value.useWrapper;
    }
}