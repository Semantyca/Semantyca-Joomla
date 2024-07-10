import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import("../views/lists/NewsletterGrid.vue")
    },
    {
        path: '/list',
        component: () => import("../views/lists/NewsletterGrid.vue")
    },
    {
        path: '/form',
        component: () => import("../views/forms/Composer.vue")
    },
    {
        path: '/form/:id',
        component: () => import("../views/forms/Composer.vue")
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;