import BaseObject from "../../utils/BaseObject";

class NewsletterApiManager extends BaseObject {
    static BASE_URL = 'index.php?option=com_semantycanm&task=newsletters.';

    constructor() {
        super();
        this.errorTimeout = 50000;
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    async fetch(currentPage, size) {
        const url = `${NewsletterApiManager.BASE_URL}findAll&page=${currentPage}&limit=${size}`;

        try {
            const response = await fetch(url, { method: 'GET' });
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return await response.json();
        } catch (error) {
            throw error;
        }
    }

    async upsert(newsletter) {
        const url = `${NewsletterApiManager.BASE_URL}upsert`;

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    ...this.headers
                },
                body: JSON.stringify(newsletter),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error(`Server error: ${response.status} - ${errorText}`);
            }

            const result = await response.json();
            console.log(result);
            return result;
        } catch (error) {
            throw error;
        }
    }

    async sendEmail(subject, messageContent, listOfUserGroups) {
        const url = `${NewsletterApiManager.BASE_URL}Service.sendEmailAsync`;
        const data = {
            subject,
            msg: messageContent,
            user_group: listOfUserGroups
        };

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
            return result;
        } catch (error) {
            throw error;
        }
    }

    async deleteNewsletters(ids) {
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
            return result;
        } catch (error) {
            throw error;
        }
    }
}

export default NewsletterApiManager;