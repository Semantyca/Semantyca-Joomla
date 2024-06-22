import {createApp, defineAsyncComponent} from 'vue';
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

const joomlaBootstrapTheme = {
    common: {
        primaryColor: '#152E52FF',
        primaryColorHover: '#0D6EFD',
        primaryColorPressed: '#0852AD',
        primaryColorSuppl: '#1E7CAB',
        infoColor: '#17A2B8',
        infoColorHover: '#138496',
        infoColorPressed: '#117A8B',
        infoColorSuppl: '#17A2B8',
        warningColor: '#FFC107',
        warningColorHover: '#E0A800',
        warningColorPressed: '#D39E00',
        warningColorSuppl: '#FFC107',
        errorColor: '#DC3545',
        errorColorHover: '#BD2130',
        errorColorPressed: '#B21F2D',
        errorColorSuppl: '#DC3545',
        successColor: '#198754',
        successColorHover: '#157347',
        successColorPressed: '#0C633A',
        successColorSuppl: '#198754'
    }
};

const loadComponent = (menuId) => {
    switch (menuId) {
        case 'newsletters':
            return defineAsyncComponent(() => import('./views/Newsletters.vue'));
        case 'mailing_lists':
            return defineAsyncComponent(() => import('./views/Lists.vue'));
        case 'stat':
            return defineAsyncComponent(() => import('./views/Statistics.vue'));
        case 'message_templates':
            return defineAsyncComponent(() => import('./views/MessageTemplates.vue'));

        default:
            return defineAsyncComponent(() => import('./views/Newsletters.vue'));
    }
}

const pinia = createPinia();
const appElement = document.getElementById('app');
const menuId = appElement.getAttribute('data-menu-id');
const DynamicComponent = loadComponent(menuId);

const app = createApp({
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

export const globalProperties = app.config.globalProperties;
