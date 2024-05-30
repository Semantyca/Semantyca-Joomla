import BaseObject from "../../utils/BaseObject";

class NewsletterApiManager extends BaseObject{
    static BASE_URL = 'index.php?option=com_semantycanm&task=NewsLetter.';

    constructor(msgPopup, loadingBar) {
        super();
        this.msgPopup = msgPopup;
        this.loadingBar = loadingBar;
        this.errorTimeout = 50000;
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    async fetch(currentPage, size) {
        this.loadingBar.start();

        const url = `${NewsletterApiManager.BASE_URL}findAll&page=${currentPage}&limit=${size}`;
        try {
            const response = await fetch(url, {
                method: 'GET',
            });
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return await response.json();
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error(error.message);
            throw error;
        } finally {
            this.loadingBar.finish();
        }
    }

    sendEmail(subj, msgContent, listOfUserGroups) {
        const data = {
            'encoded_body': encodeURIComponent(msgContent),
            'subject': subj,
            'user_group': listOfUserGroups
        };
        return this.doPostRequest('Service.sendEmailAsync', data);
    }

    saveNewsletter(subj, msgContent) {
        const data = {
            'subject': subj,
            'msg': encodeURIComponent(msgContent)
        };
        return this.doPostRequest('NewsLetter.add', data);
    }

    async doPostRequest(endpoint, data) {
        super.startBusyMessage('Sending to the queue ...')
        const url = `index.php?option=com_semantycanm&task=${endpoint}`;
        try {
            const response = await fetch(url, {
                method: 'POST',
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

export default NewsletterApiManager;