import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "../layouts/AdminLayout.vue";
import ClientLayout from "../layouts/ClientLayout.vue";

const routes = [
    {
        path: '/',
        component: ClientLayout,
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('../pages/HomeView.vue')
            },
            {
                path: '/login',
                name: 'login',
                component: () => import('../pages/LoginAdmin.vue')
            },
            {
                path: '/register',
                name: 'register',
                component: () => import('../pages/Register.vue')
            }

        ]
    },
    {
        path: '/admin',
        component: AdminLayout,
        children: [
            {
                path: '',
                name: 'admin',
                component: () => import('../pages/DashboardAdmin.vue')
            },
            {
                path: 'categories',
                name: 'admin_categories',
                component: () => import('../pages/CategoriesListAdmin.vue')
            },
        ],
    }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;

