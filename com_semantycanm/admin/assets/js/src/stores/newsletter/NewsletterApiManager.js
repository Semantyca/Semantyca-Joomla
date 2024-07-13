import BaseObject from "../../utils/BaseObject";

class NewsletterApiManager extends BaseObject {
    static BASE_URL = 'index.php?option=com_semantycanm&task=newsletters.';
    static SERVICE_BASE_URL = 'index.php?option=com_semantycanm&task=service.';

    constructor() {
        super();
        this.errorTimeout = 50000;
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    async fetchPage(currentPage, size) {
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

    async fetch(id) {
        const url = `${NewsletterApiManager.BASE_URL}find&id=${id}`;

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

    async upsert(newsletter, id) {
        let url = NewsletterApiManager.BASE_URL + 'upsert';

        if (id) {
            url = url + '&id=' + id;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: this.headers,
                body: JSON.stringify(newsletter),
            });

            if (!response.ok) {
                const errorText = await response.text();
                throw new Error('Server error: ' + response.status + ' - ' + errorText);
            }

            return await response.json();
        } catch (error) {
            throw error;
        }
    }

    async sendEmail(newsletter, id) {
        let url = `${NewsletterApiManager.SERVICE_BASE_URL}sendEmailAsync`;

        if (id) {
            url = url + '&id=' + id;
        }

        try {
            const response = await fetch(url, {
                method: 'POST',
                headers: this.headers,
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