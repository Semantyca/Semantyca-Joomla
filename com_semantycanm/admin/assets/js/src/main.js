import {createApp} from 'vue';
import {createPinia} from 'pinia';
import Workspace from "./views/Workspace.vue";
import {darkTheme, lightTheme, NConfigProvider, NGlobalStyle, NMessageProvider} from "naive-ui";

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
        NGlobalStyle,
        NConfigProvider,
        Workspace,
        NMessageProvider
    },
    template: `
      <div>
        <n-message-provider>
          <n-config-provider :theme-overrides="smtcaTheme">
            <!-- <n-global-style />-->
            <Workspace/>
          </n-config-provider>
        </n-message-provider>
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

app.use(pinia);
app.mount('#app');





