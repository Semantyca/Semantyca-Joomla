class CustomField {
    constructor(id, name, type, caption, defaultValue, isAvailable = false) {
        this.id = id;
        this.name = name;
        this.type = type;
        this.caption = caption;
        this.defaultValue = defaultValue;
        this.isAvailable = isAvailable;
    }
}

class Doc {
    constructor(id, regDate, name, type, description, content, wrapper, customFields = []) {
        this.id = id;
        this.regDate = regDate;
        this.name = name;
        this.type = type;
        this.description = description;
        this.content = content;
        this.wrapper = wrapper;
        this.customFields = customFields;
    }
}
