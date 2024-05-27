class MailingListRequest {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList.';
    static HTTP_METHOD = 'POST';

    static upsert(id, mailingListName, listItems) {
        const operation = id === -1 ? 'add' : 'update';

        fetch(MailingListRequest.BASE_URL + operation, {
            method: MailingListRequest.HTTP_METHOD,
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                'id': id,
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
                console.log('MailingList.' + operation + ':', error);
                showErrorBar('MailingList.' + operation, error.message);
            });
    }
}

export default MailingListRequest;
