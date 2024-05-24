

class MailingListRequest {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList.';

    constructor(id) {
        this.id = id;
        this.operation = this.id === '' ? 'add' : 'update';
        this.httpMethod = 'POST';
    }

    process(mailingListName, listItems) {
        fetch(MailingListRequest.BASE_URL + this.operation, {
            method: this.httpMethod,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'id': this.id,
                'mailinglistname': mailingListName,
                'mailinglists': listItems.join(',')
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error, status = ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.log('MailingList.' + this.operation + ':', error);
                showErrorBar('MailingList.' + this.operation, error.message);
            });
    }
}

export default MailingListRequest;