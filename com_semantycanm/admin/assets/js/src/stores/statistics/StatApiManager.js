import BaseObject from "../../utils/BaseObject";

export default class StatApiManager extends BaseObject {
    static BASE_URL = 'index.php?option=com_semantycanm&task=Stat.';

    constructor(msgPopup, loadingBar) {
        super();
        this.msgPopup = msgPopup;
        this.loadingBar = loadingBar;
        this.errorTimeout = 50000;
    }

    async fetch(currentPage, size) {
        this.loadingBar.start();

        const url = `${StatApiManager.BASE_URL}findAll&page=${currentPage}&limit=${size}`;
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
}
