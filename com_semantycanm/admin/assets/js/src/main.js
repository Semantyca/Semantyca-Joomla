import {createApp} from 'vue';
import {createPinia} from 'pinia';
import Workspace from "./views/Workspace.vue";
import {
    darkTheme,
    lightTheme,
    NConfigProvider,
    NDialogProvider,
    NGlobalStyle,
    NLoadingBarProvider,
    NMessageProvider
} from "naive-ui";
import '../../tailwind.css';

const smtcaTheme = {
    common: {
        //  fontSize: "18px",
        //  fontSizeSmall: "18px",
        //  fontSizeMedium: "18px",
        //  fontSizeLarge: "22px",
        //   fontSizeHuge: "22px",
        primaryColor: "#152E52FF"
    }
};

const pinia = createPinia();

const app = createApp({
    components: {
        NLoadingBarProvider,
        NGlobalStyle,
        NConfigProvider,
        Workspace,
        NMessageProvider,
        NDialogProvider
    },
    template: `
      <div>
        <n-loading-bar-provider>
          <n-message-provider>
            <n-dialog-provider>
              <n-config-provider :theme-overrides="smtcaTheme">
                <!-- <n-global-style />-->
                <Workspace/>
              </n-config-provider>
            </n-dialog-provider>
          </n-message-provider>
        </n-loading-bar-provider>
      </div>
    `,
    setup() {
        return {
            smtcaTheme,
            lightTheme,
            darkTheme
        };
    },
});
app.config.globalProperties.$errorTimeout = 10000;
app.use(pinia);
app.mount('#app');





