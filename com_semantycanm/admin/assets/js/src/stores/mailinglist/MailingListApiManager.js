import BaseObject from "../../utils/BaseObject";

export default class MailingListApiManager extends BaseObject {
    static BASE_URL = 'index.php?option=com_semantycanm&task=MailingList.';

    constructor(msgPopup, loadingBar) {
        super();
        this.msgPopup = msgPopup;
        this.loadingBar = loadingBar;
        this.errorTimeout = 50000;
    }

    async fetch(currentPage, size) {
        this.loadingBar.start();

        const url = `${MailingListApiManager.BASE_URL}findAll&page=${currentPage}&limit=${size}`;
        try {
            const response = await fetch(url, {
                method: 'GET',
            });
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            return await response.json();
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error(error.message);
            throw error;
        } finally {
            this.loadingBar.finish();
        }
    }

    async fetchDetails(id, detailed = false) {
        this.loadingBar.start();

        const url = `${MailingListApiManager.BASE_URL}find&id=${encodeURIComponent(id)}&detailed=${detailed}`;
        try {
            const response = await fetch(url, {
                method: 'GET',
            });
            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }
            const respData = await response.json();
            if (respData.success) {
                return respData.data;
            } else {
                throw new Error('Error from server: ' + (respData.message || 'Unknown error'));
            }
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
            throw error;
        } finally {
            this.loadingBar.finish();
        }
    }

    async delete(ids) {
        this.loadingBar.start();
        const idsParam = ids.join(',');
        const url = `${MailingListApiManager.BASE_URL}delete&ids=${encodeURIComponent(idsParam)}`;

        try {
            const response = await fetch(url, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error('Network response was not ok: ' + response.statusText);
            }

            const respData = await response.json();

            if (respData.success) {
                this.msgPopup.info('The mailing list deleted');
            } else {
                throw new Error('Error from server: ' + (respData.message || 'Unknown error'));
            }
        } catch (error) {
            this.loadingBar.error();
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.loadingBar.finish();
        }
    }

    async upsert(mailingListName, listItems, id) {
        this.loadingBar.start();
        const operation = id === -1 ? 'add' : 'update';

        fetch(MailingListApiManager.BASE_URL + operation, {
            method: 'POST',
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
                this.loadingBar.error();
                this.msgPopup.error(error.message, {
                    closable: true,
                    duration: this.errorTimeout
                });
            })
            .finally(() => {
                this.loadingBar.finish();
            });
    }
}
