import { createRouter, createWebHashHistory } from 'vue-router';

const routes = [
    { path: '/', component: () => import("../views/lists/TemplateGrid.vue") },
    { path: '/list', component: () => import("../views/lists/TemplateGrid.vue") },
    { path: '/form', component: () => import("../views/forms/TemplateEditor.vue") },
    { path: '/form/:id', component: () => import("../views/forms/TemplateEditor.vue") },
];

const router = createRouter({
    history: createWebHashHistory(),
    routes,
});

export default router;