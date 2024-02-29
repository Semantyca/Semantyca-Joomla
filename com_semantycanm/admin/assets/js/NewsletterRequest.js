class NewsletterRequest {
    constructor() {
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
        return this.makeRequest('Service.sendEmailAsync', data);
    }

    addNewsletter(subj, msgContent) {
        const data = {
            'subject': subj,
            'msg': encodeURIComponent(msgContent)
        };
        return this.makeRequest('NewsLetter.add', data);
    }

    makeRequest(endpoint, data) {
        const url = `index.php?option=com_semantycanm&task=${endpoint}`;
        return fetch(url, {
            method: this.httpMethod,
            headers: this.headers,
            body: JSON.stringify(data),
        })
            .then(response => {
                if (!response.ok) {
                    console.log('application error: ', response.text())
                    throw new Error('Server returned an application error: ' + response.status);
                }
                return response.json();
            });
    }
}
