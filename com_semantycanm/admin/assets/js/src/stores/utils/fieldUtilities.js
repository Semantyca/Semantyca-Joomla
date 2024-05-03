export function adaptField(field) {
    switch (field.type) {
        case 503:
            try {
                const parsedValue = JSON.parse(field.defaultValue);
                return {
                    ...field,
                    defaultValue: Array.isArray(parsedValue) ? parsedValue : []
                };
            } catch (error) {
                return {
                    ...field,
                    defaultValue: []
                };
            }
        case 504:
            return {
                ...field,
                defaultValue: field.defaultValue.replace(/^"|"$/g, "")
            };
        case 501:
            return {
                ...field,
                defaultValue: Number(field.defaultValue)
            };
        default:
            return {...field};
    }
}

export function processFormCustomFields(rawFields, adaptField) {
    return rawFields.reduce((acc, field) => {
        if (field.isAvailable === 1) {
            const key = field.name;
            acc[key] = adaptField(field);
        }
        return acc;
    }, {});
}
