export default class UserExperienceHelper {
    static async getSubject(loadingBar) {
        try {
            loadingBar.start();
            const response = await fetch('index.php?option=com_semantycanm&task=service.getSubject&type=random');
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
