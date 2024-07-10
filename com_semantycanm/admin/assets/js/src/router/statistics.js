import { createRouter, createWebHashHistory } from 'vue-router';
import StatsGrid from "../views/lists/StatsGrid.vue";
import StatsDetails from "../views/forms/StatsDetails.vue";

const routes = [
    { path: '/', component: StatsGrid },
    { path: '/list', component: StatsGrid },
    { path: '/form', component: StatsDetails },
    { path: '/form/:id', component: StatsDetails},
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;