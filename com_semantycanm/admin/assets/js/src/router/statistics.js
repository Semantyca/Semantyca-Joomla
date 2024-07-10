import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    {
        path: '/',
        component: () => import("../views/lists/StatsGrid.vue")
    },
    {
        path: '/list',
        component: () => import("../views/lists/StatsGrid.vue")
    },
    {
        path: '/form',
        component: () => import("../views/forms/StatsDetails.vue")
    },
    {
        path: '/form/:id',
        component: () => import("../views/forms/StatsDetails.vue")
    },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;