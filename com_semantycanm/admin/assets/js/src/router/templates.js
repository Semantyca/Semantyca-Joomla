import { createRouter, createWebHashHistory } from 'vue-router';
import MessageTemplateGrid from "../views/lists/MessageTemplateGrid.vue";
import MessageTemplateEditor from "../views/forms/MessageTemplateEditor.vue";

const routes = [
    { path: '/', component: MessageTemplateGrid },
    { path: '/list', component: MessageTemplateGrid },
    { path: '/form', component: MessageTemplateEditor },
    { path: '/form/:id', component: MessageTemplateEditor},
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;