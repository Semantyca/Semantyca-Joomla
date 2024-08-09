export default class MailingListApiManager {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList';

    async fetch(currentPage, size) {
        const url = `${MailingListApiManager.BASE_URL}.findAll&page=${currentPage}&size=${size}`;
        const response = await fetch(url, {
            method: 'GET',
        });
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return await response.json();
    }

    async fetchDetails(id, detailed = false) {
        const url = `${MailingListApiManager.BASE_URL}.find&id=${encodeURIComponent(id)}&detailed=${detailed}`;
        const response = await fetch(url, {
            method: 'GET',
        });
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return await response.json();
    }

    async delete(ids) {
        const url = `${MailingListApiManager.BASE_URL}.delete`;
        const response = await fetch(url, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ids})
        });

        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.statusText);
        }
        return await response.json();
    }

    async upsert(mailingListName, listItems, id = null) {
        const url = `${MailingListApiManager.BASE_URL}.upsert&id=${id ? id : -1}`;
        return await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                mailinglistname: mailingListName,
                mailinglists: listItems
            })
        });
    }
}
