import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import("../views/lists/MailingListGrid.vue")
    },
    {
        path: '/list',
        component: () => import("../views/lists/MailingListGrid.vue")
    },
    {
        path: '/form',
        component: () => import("../views/forms/MailingListEditor.vue")
    },
    {
        path: '/form/:id',
        component: () => import("../views/forms/MailingListEditor.vue")
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;