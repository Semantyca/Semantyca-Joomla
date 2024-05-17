import BaseObject from "./BaseObject";

class NewsletterHandler extends BaseObject{

    constructor(msgPopup) {
        super();
        this.msgPopup = msgPopup;
        this.errorTimeout = 50000;
        this.httpMethod = 'POST';
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    sendEmail(subj, msgContent, listOfUserGroups) {
        const data = {
            'encoded_body': encodeURIComponent(msgContent),
            'subject': subj,
            'user_group': listOfUserGroups
        };
        return this.doRequest('Service.sendEmailAsync', data);
    }

    saveNewsletter(subj, msgContent) {
        const data = {
            'subject': subj,
            'msg': encodeURIComponent(msgContent)
        };
        return this.doRequest('NewsLetter.add', data);
    }

    async doRequest(endpoint, data) {
        super.startBusyMessage('Sending to the queue ...')
        const url = `index.php?option=com_semantycanm&task=${endpoint}`;
        try {
            const response = await fetch(url, {
                method: this.httpMethod,
                headers: this.headers,
                body: JSON.stringify(data),
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }
            return await response.json();
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            super.stopBusyMessage()
        }
    }

}

export default NewsletterHandler;