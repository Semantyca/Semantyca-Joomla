export default class SourceEntity {
    constructor(id, title, category, url, intro) {
        this.id = id;
        this.value = id;
        this.title = title;
        this.category = category;
        this.url = url;
        this.intro = intro;
    }

    get key() {
        return this.id;
    }

    get label() {
        return this.title;
    }
}