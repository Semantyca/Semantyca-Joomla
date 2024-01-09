class MailingListRequest {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList.';

    constructor(mode) {
        this.mode = mode;
        this.operation = this.mode === 'editing' ? 'update' : 'add';
        this.httpMethod = this.mode === 'editing' ? 'PUT' : 'POST';
    }

    process(mailingListName, listItems) {
        showSpinner('listSpinner');

        fetch(MailingListRequest.BASE_URL + this.operation, {
            method: this.httpMethod,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
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
                showInfoBar("The mailing list updated successfully");
            })
            .catch(error => {
                console.log('MailingList.' + this.operation + ':', error);
                showErrorBar('MailingList.' + this.operation, error.message);
            })
            .finally(() => {
                refreshMailingList(1);
                if (this.mode === '') {
                    getPageOfMailingList();
                } else {
                    document.getElementById('mailingListMode').value = '';
                }
                document.getElementById('mailingListName').value = '';
                hideSpinner('listSpinner');
            });
    }


}
