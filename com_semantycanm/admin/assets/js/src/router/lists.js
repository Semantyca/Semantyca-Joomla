import { createRouter, createWebHashHistory } from 'vue-router';
import MailingListGrid from "../views/lists/MailingListGrid.vue";
import MailingListEditor from "../views/forms/MailingListEditor.vue";

const routes = [
    { path: '/', component: MailingListGrid },
    { path: '/list', component: MailingListGrid },
    { path: '/form', component: MailingListEditor },
    { path: '/form/:id', component: MailingListEditor},
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;