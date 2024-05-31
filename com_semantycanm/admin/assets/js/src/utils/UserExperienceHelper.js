export default class UserExperienceHelper {
    static async getSubject(loadingBar) {
        try {
            loadingBar.start();
            const params = {
                type: 'random'
            };
            const response = await fetch('index.php?option=com_semantycanm&task=service.getSubject&type=random', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(params)
            });
            if (!response.ok) {
                throw new Error(`HTTP error, status = ${response.status}`);
            }
            const data = await response.json();
            return data.data;
        } catch (error) {
            console.log(error);
            throw error;
        } finally {
            loadingBar.finish();
        }
    }
}
