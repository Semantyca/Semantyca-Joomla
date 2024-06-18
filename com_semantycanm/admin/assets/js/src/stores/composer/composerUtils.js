import { isEmail } from 'validator';

export const composerFormRules = {
    subject: {
        required: true,
        message: 'Subject cannot be empty',
    },
    mailing_list: {
        required: true,
        validator(rule, value) {
            if (Array.isArray(value) && value.length > 0) {
                return true;
            } else if (typeof value === 'string' && isEmail(value)) {
                return true;
            }
            return new Error('Must be a valid email or non-empty mailing list');
        },
        trigger: 'blur'
    }
};
