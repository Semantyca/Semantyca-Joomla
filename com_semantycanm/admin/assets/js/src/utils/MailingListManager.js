class MailingListManager {
    constructor(store, msgPopup) {
        this.store = store;
        this.msgPopup = msgPopup;
        this.errorTimeout = 50000;
    }

    async fetchMailingLists(currentPage = 1, itemsPerPage = 10) {
        this.startBusyMessage('Fetching mailing lists...');
        const url = `index.php?option=com_semantycanm&task=MailingList.findAll&page=${currentPage}&limit=${itemsPerPage}`;
        try {
            const response = await fetch(url);
            if (!response.ok) {
                throw new Error(`Failed to fetch mailing lists, HTTP status = ${response.status}`);
            }
            const data = await response.json();
            if (data.success && data.data) {
                this.store.setMailingLists(data.data.docs, data.data);
            } else {
                throw new Error('Failed to fetch mailing lists: No data returned');
            }
        } catch (error) {
            this.msgPopup.error('Error fetching mailing lists: ' + error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage();
        }
    }

    async saveMailingList(doc, isNew = false) {
        this.startBusyMessage('Saving mailing list...');
        const endpoint = isNew ? `index.php?option=com_semantycanm&task=MailingList.save` :
            `index.php?option=com_semantycanm&task=MailingList.update&id=${encodeURIComponent(doc.id)}`;
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(doc)
            });
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const result = await response.json();
            this.msgPopup.success(result.message);
            return result;
        } catch (error) {
            this.msgPopup.error(error.message, {
                closable: true,
                duration: this.errorTimeout
            });
        } finally {
            this.stopBusyMessage();
        }
    }

    startBusyMessage(msg) {
        if (!this.messageReactive) {
            this.messageReactive = this.msgPopup.loading(msg, {
                duration: 0
            });
        }
    }

    stopBusyMessage() {
        if (this.messageReactive) {
            this.messageReactive.destroy();
            this.messageReactive = null;
        }
    }
}

export default MailingListManager;
