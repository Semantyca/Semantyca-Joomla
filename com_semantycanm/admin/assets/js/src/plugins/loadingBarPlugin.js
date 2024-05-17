// loadingBarPlugin.js
import {useLoadingBar} from 'naive-ui';

export default {
    install(app) {
        const loadingBar = useLoadingBar();
        app.provide('loadingBar', loadingBar);
        app.config.globalProperties.$loadingBar = loadingBar;
    }
};
