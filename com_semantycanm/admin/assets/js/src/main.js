import {createApp} from 'vue';
import TemplateEditor from './views/TemplateEditor.vue';
import Composer from './views/Composer.vue';
import Statistics from "./views/Statistics.vue";
import {createPinia} from 'pinia';

const pinia = createPinia();

const composerApp = createApp(Composer);
composerApp.use(pinia);
composerApp.mount('#composerSection');

const statApp = createApp(Statistics);
statApp.use(pinia);
statApp.mount('#statSection');

const app = createApp(TemplateEditor);
app.mount('#templateSection');


