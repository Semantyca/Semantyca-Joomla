import {createApp} from 'vue';
import {createPinia} from 'pinia';
import Workspace from "./views/Workspace.vue";
import {
    NConfigProvider,
    NDialogProvider,
    NGlobalStyle,
    NLoadingBarProvider,
    NMessageProvider
} from "naive-ui";
import '../../tailwind.css';
import Newsletters from './views/Newsletters.vue';
import Lists from './views/Lists.vue';
import Statistics from './views/Statistics.vue';
import MessageTemplates from './views/MessageTemplates.vue';
import {joomlaBootstrapTheme} from "./theme";

const loadComponent = (menuId) => {
    switch (menuId) {
        case 'newsletters':
            return {component: Newsletters, name: 'NewslettersApp'};
        case 'mailing_lists':
            return {component: Lists, name: 'MailingListsApp'};
        case 'stat':
            return {component: Statistics, name: 'StatisticsApp'};
        case 'messagetemplates':
            return {component: MessageTemplates, name: 'MessageTemplatesApp'};
        default:
            return {component: Newsletters, name: 'DefaultApp'};
    }
}

const pinia = createPinia();
const appElement = document.getElementById('app');
const menuId = appElement.getAttribute('data-menu-id');
const {component: DynamicComponent, name: appName} = loadComponent(menuId);

const app = createApp(
    {
    name: appName,
    components: {
        NLoadingBarProvider,
        NGlobalStyle,
        NConfigProvider,
        Workspace,
        NMessageProvider,
        NDialogProvider,
        DynamicComponent
    },
    template: `
      <div>
        <n-loading-bar-provider>
          <n-message-provider>
            <n-dialog-provider>
              <n-config-provider :theme-overrides="smtcaTheme">
                <DynamicComponent/>
              </n-config-provider>
            </n-dialog-provider>
          </n-message-provider>
        </n-loading-bar-provider>
      </div>
    `,
    setup() {
        return {
            smtcaTheme: joomlaBootstrapTheme,
        };
    },
});

app.config.globalProperties.$errorTimeout = 10000;
app.use(pinia);
app.mount('#app');