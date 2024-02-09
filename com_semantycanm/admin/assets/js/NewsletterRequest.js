class NewsletterRequest {
    constructor() {
        this.httpMethod = 'POST';
        this.headers = new Headers({
            "Content-Type": "application/x-www-form-urlencoded"
        });
    }

    async makeRequest(endpoint, data) {
        try {
            const url = `index.php?option=com_semantycanm&task=${endpoint}`;
            const formData = new URLSearchParams();

            for (const key in data) {
                if (data.hasOwnProperty(key)) {
                    formData.append(key, data[key]);
                }
            }

            const response = await fetch(url, {
                method: this.httpMethod,
                headers: this.headers,
                body: formData,
            });

            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }

            return await response.json();
        } catch (error) {
            showErrorBar(`${endpoint}`, error.message);
            throw error;
        }
    }

    sendEmail(subj, msgContent, listOfUserGroups) {
        const data = {
            'encoded_body': encodeURIComponent(msgContent),
            'subject': subj,
            'user_group': listOfUserGroups
        };
        return this.makeRequest('Service.sendEmail', data);
    }

    addNewsletter(subj, msgContent) {
        const data = {
            'subject': subj,
            'msg': encodeURIComponent(msgContent)
        };
        return this.makeRequest('NewsLetter.add', data);
    }
}