import { createApp } from 'vue';
import { createPinia } from 'pinia';
import {
    NConfigProvider,
    NDialogProvider,
    NGlobalStyle,
    NLoadingBarProvider,
    NMessageProvider
} from "naive-ui";
import '../../tailwind.css';
import { joomlaBootstrapTheme } from "./theme";

const loadComponent = async (menuId) => {
    switch (menuId) {
        case 'newsletters':
            return {
                component: (await import('./views/lists/NewsletterGrid.vue')).default,
                router: (await import('./router/newsletters')).default,
                name: 'Newsletters'
            };
        case 'mailing_lists':
            return {
                component: (await import('./views/lists/MailingListGrid.vue')).default,
                router: (await import('./router/lists')).default,
                name: 'Lists'
            };
        case 'stat':
            return {
                component: (await import('./views/lists/StatsGrid.vue')).default,
                router: (await import('./router/statistics')).default,
                name: 'Statistics'
            };
        case 'message_templates':
            return {
                component: (await import('./views/lists/MessageTemplateGrid.vue')).default,
                router: (await import('./router/templates')).default,
                name: 'MessageTemplates'
            };
        default:
            return {
                component: (await import('./views/lists/NewsletterGrid.vue')).default,
                router: (await import('./router/newsletters')).default,
                name: 'Newsletters'
            };
    }
};

const pinia = createPinia();
const appElement = document.getElementById('app');
let currentApp = null;

const mountApp = async (menuId) => {
    console.log('Mounting app for menuId:', menuId);

    if (currentApp) {
        console.log('Unmounting previous app');
        currentApp.unmount();
        currentApp = null;
    }

    const { component: DynamicComponent, router, name: appName } = await loadComponent(menuId);

    const app = createApp({
        name: appName,
        components: {
            NLoadingBarProvider,
            NGlobalStyle,
            NConfigProvider,
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
                    <router-view/>
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
    app.use(router);

    app.mount('#app');
    currentApp = app;
  //  await router.push('/');
};

const initialMenuId = appElement.getAttribute('data-menu-id');
mountApp(initialMenuId);

