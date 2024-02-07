class MailingListRequest {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList.';

    constructor(id) {
        this.id = id;
        this.operation = this.id === '' ? 'add' : 'update';
        this.httpMethod = 'POST';
    }

    process(mailingListName, listItems) {
        // showSpinner('listSpinner');

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
                //showInfoBar("The mailing list updated successfully");
            })
            .catch(error => {
                console.log('MailingList.' + this.operation + ':', error);
                showErrorBar('MailingList.' + this.operation, error.message);
            })
            .finally(() => {
                //  refreshMailingList(1);
                if (this.id === '') {
                    // getPageOfMailingList();
                } else {
                    this.id = '';
                    document.getElementById('mailingListMode').value = '';
                }
                document.getElementById('mailingListName').value = '';
                //   hideSpinner('listSpinner');
            });
    }


}
