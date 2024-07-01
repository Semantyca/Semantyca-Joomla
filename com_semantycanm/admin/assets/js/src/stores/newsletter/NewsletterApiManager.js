import BaseObject from "../../utils/BaseObject";

class NewsletterApiManager extends BaseObject {
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
            'subject': subj,
            'msg': msgContent,
            'user_group': listOfUserGroups
        };
        return this.doPostRequest('Service.sendEmailAsync', data, 'Sending');
    }

    saveNewsletter(subj, msgContent) {
        const data = {
            'subject': subj,
            'msg': msgContent
        };
        return this.doPostRequest('Newsletters.add', data, 'Saving');
    }

    async deleteNewsletters(ids) {
        this.loadingBar.start();
        const url = `${NewsletterApiManager.BASE_URL}delete`;

        try {
            const response = await fetch(url, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    ...this.headers
                },
                body: JSON.stringify({ ids }),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log(result);
            this.msgPopup.success('Newsletters deleted successfully!', {
                closable: true,
                duration: 5000
            });
            return result;
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
            throw error;
        } finally {
            this.loadingBar.finish();
        }
    }

    async doPostRequest(endpoint, data, actionType) {
        super.startBusyMessage(actionType + '...');
        const url = `index.php?option=com_semantycanm&task=${endpoint}`;
        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...this.headers
                },
                body: JSON.stringify(data),
            });
            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }
            const result = await response.json();
            console.log(result);
            this.msgPopup.success('Operation completed successfully!', {
                closable: true,
                duration: 5000
            });
            return result;
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
            throw error;
        } finally {
            super.stopBusyMessage();
        }
    }
}

export default NewsletterApiManager;
