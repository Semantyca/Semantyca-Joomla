import {useMessage} from "naive-ui";

class NewsletterApiManager  {
    static BASE_URL = 'index.php?option=com_semantycanm&task=newsletters';
    static SERVICE_BASE_URL = 'index.php?option=com_semantycanm&task=service';

    constructor() {
        this.msgPopup = useMessage();
        this.headers = new Headers({
            'Content-Type': 'application/json',
        });
    }

    async fetch(id) {
        return this.fetchData(`${NewsletterApiManager.BASE_URL}.find&id=${id}`);
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

    handleError(error) {
        console.error('API Error:', error);
        this.msgPopup.error(error.message || 'An unexpected error occurred');
        throw error;
    }
}

export default NewsletterApiManager;