export class Template {
    constructor(id = 0,
                name = '',
                type = '',
                description = '',
                content = '',
                wrapper = '',
                isDefault = false,
                customFields = []) {
        this.id = id;
        this.name = name;
        this.type = type;
        this.description = description;
        this.content = content;
        this.wrapper = wrapper;
        this.isDefault = isDefault;
        this.customFields = customFields.map(cf => new CustomField(cf));
    }
}

export class CustomField {
    constructor({
                    id = 0,
                    name = '',
                    type = '',
                    caption = '',
                    defaultValue = '',
                    isAvailable = false
                }) {
        this.id = id;
        this.name = name;
        this.type = type;
        this.caption = caption;
        this.defaultValue = defaultValue;
        this.isAvailable = isAvailable;
    }
}