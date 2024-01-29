import {createApp} from 'vue';
import {createPinia} from 'pinia';
import Workspace from "./views/Workspace.vue";
import {NConfigProvider, NGlobalStyle} from "naive-ui";

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
    },
    template: `

      <div>
        <n-config-provider :theme-overrides="smtcaTheme">
          <!-- <n-global-style />-->
          <Workspace/>
        </n-config-provider>

      </div>

    `,
    setup() {
        return {
            smtcaTheme,
        };
    },
});

app.use(pinia);
app.mount('#app');





