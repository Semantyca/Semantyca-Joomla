import {createApp} from 'vue';
import TemplateEditor from './views/TemplateEditor.vue';
import Composer from './views/Composer.vue';

const app = createApp(TemplateEditor);
app.mount('#templateSection');

const composerApp = createApp(Composer);
composerApp.mount('#composerSection');
