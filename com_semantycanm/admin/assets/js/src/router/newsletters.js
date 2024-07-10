import { createRouter, createWebHashHistory } from 'vue-router';
import NewsletterGrid from "../views/lists/NewsletterGrid.vue";
import Composer from "../views/forms/Composer.vue";

const routes = [
    { path: '/', component: NewsletterGrid },
    { path: '/list', component: NewsletterGrid },
    { path: '/form', component: Composer },
    { path: '/form/:id', component: Composer},
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;