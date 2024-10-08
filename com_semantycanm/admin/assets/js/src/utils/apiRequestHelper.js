export function handleError(msgPopup, error) {
    console.error('Network Error:', error);
    msgPopup.error(error.message || 'An unexpected error occurred');
    throw error;
}

export async function handleNotOkError(msgPopup, response) {
    const responseData = await response.json();
    console.log('API Error: ', responseData);
    const errorMessages = parseErr(responseData);
    if (response.status === 422) {
        msgPopup.warning(
            `Validation Error:\n${errorMessages}`,
            {closable: true, duration: 5000}
        );
    } else {
        msgPopup.error(errorMessages, {closable: true, duration: 5000});
    }
}

export async function handleSuccess(msgPopup, response) {
    const responseData = await response.json();
    msgPopup.success(responseData.message, {closable: true, duration: 5000});
}

export function parseErr(responseData) {
    return  responseData.details.map(error => `${error}`).join('\n');
}