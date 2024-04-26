import {defineStore} from 'pinia';

export const useGlobalStore = defineStore('global', {
    state: () => ({
        translations: window.globalTranslations || {},
    }),
    actions: {}
});
