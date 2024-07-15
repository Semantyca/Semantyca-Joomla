
export const rules = {
    templateName: {
        required: true,
        message: 'Template name cannot be empty',
    },
    templateType: {
        required: true,
        message: 'Template type cannot be empty',
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
    {label: "Articles", value: 520},
    {label: "Articles", value: 521},
];

function isValidJson(value) {
    try {
        JSON.parse(value);
        return true;
    } catch (error) {
        return false;
    }
}
