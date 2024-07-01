import { isEmail } from 'validator';

export const composerFormRules = {
    subject: {
        required: true,
        message: 'Subject cannot be empty',
        trigger: ['blur', 'input']
    },
    selectedArticles: {
        required: true,
        validator(rule, value) {
            if (Array.isArray(value) && value.length > 0) {
                return true;
            }
            return new Error('Please select at least one article');
        },
        trigger: ['blur', 'change']
    },
    recipientField: {
        required: true,
        validator(rule, value, model) {
            if (model.isTestMessage) {
                if (isEmail(model.testUserEmail)) {
                    return true;
                }
                return new Error('Please enter a valid email address for test user');
            } else {
                if (Array.isArray(model.mailingList) && model.mailingList.length > 0) {
                    return true;
                }
                return new Error('Please select at least one mailing list');
            }
        },
        trigger: ['blur', 'change']
    }
};