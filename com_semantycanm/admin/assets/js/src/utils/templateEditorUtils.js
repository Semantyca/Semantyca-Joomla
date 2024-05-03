export function getTypedValue(value, type) {
    switch (type) {
        case 501:
            return String(value);
        case 503:
            return String(value);
        case 502:
        default:
            return value;
    }
}

export function setTypedValue(customFormFields, index, stringValue) {
    const fieldType = customFormFields[index].type;
    let convertedValue = stringValue;
    switch (fieldType) {
        case 501:
            convertedValue = Number(stringValue);
            break;
        case 503:
            convertedValue = stringValue;
            break;
        case 502:
        default:
            convertedValue = stringValue;
    }
    customFormFields[index].defaultValue = convertedValue;
}

export const rules = {
    templateName: {
        required: true,
        message: 'Template name cannot be empty',
    },
    defaultValue: [
        {required: true, message: 'Default value cannot be empty'},
        {validator: (rule, value) => isValidJson(value), message: 'Please enter a valid JSON string'},
    ],
    variableName: {
        required: true,
        message: 'Variable name cannot be empty',
    },
    valueType: {
        required: true,
        message: 'Value type cannot be empty',
    },
    caption: {
        required: true,
        message: 'Caption cannot be empty',
    },
};

export const typeOptions = [
    {label: "Number", value: 501},
    {label: "String", value: 502},
    {label: "Colors", value: 503},
    {label: "URL", value: 504},
];

function isValidJson(value) {
    try {
        JSON.parse(value);
        return true;
    } catch (error) {
        return false;
    }
}
