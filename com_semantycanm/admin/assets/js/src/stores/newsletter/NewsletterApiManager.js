import BaseObject from "../../utils/BaseObject";
import { useMessage } from "naive-ui";

class NewsletterApiManager extends BaseObject {
    static BASE_URL = 'index.php?option=com_semantycanm&task=newsletters';
    static SERVICE_BASE_URL = 'index.php?option=com_semantycanm&task=service';

    constructor() {
        super();
        this.msgPopup = useMessage();
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    async fetchPage(currentPage, size) {
        return this.fetchData(`${NewsletterApiManager.BASE_URL}.findAll&page=${currentPage}&limit=${size}`);
    }

    async fetch(id) {
        return this.fetchData(`${NewsletterApiManager.BASE_URL}.find&id=${id}`);
    }

    async upsert(newsletter) {
        const url = `${NewsletterApiManager.BASE_URL}upsert${newsletter.id ? '&id=' + newsletter.id : ''}`;
        return this.sendRequest(url, 'POST', newsletter);
    }

    async sendEmail(newsletter) {
        const url = `${NewsletterApiManager.SERVICE_BASE_URL}.sendEmailAsync${newsletter.id ? '&id=' + newsletter.id : ''}`;
        return this.sendRequest(url, 'POST', newsletter);
    }

    async deleteNewsletters(ids) {
        return this.sendRequest(`${NewsletterApiManager.BASE_URL}delete`, 'DELETE', { ids });
    }

    async fetchData(url) {
        try {
            const response = await fetch(url, { method: 'GET' });
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            this.handleError(error);
        }
    }

    async sendRequest(url, method, data) {
        try {
            const response = await fetch(url, {
                method,
                headers: this.headers,
                body: JSON.stringify(data),
            });
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            return await response.json();
        } catch (error) {
            this.handleError(error);
        }
    }

    handleError(error) {
        console.error('API Error:', error);
        this.msgPopup.error(error.message || 'An unexpected error occurred');
        throw error;
    }
}

export default NewsletterApiManager;