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
            // San pham
            {
                path: '/product',
                name: 'product',
                component: () => import('../pages/Product.vue')
            },
            // Tin tuc
            {
                path: '/blog',
                name: 'blog',
                component: () => import('../pages/Blog.vue')
            },
            //
            {
                path: '/blog/:id',
                name: 'blog_detail',
                component: () => import('../pages/BlogDetail.vue')
            },
            // Store
            {
                path: '/store',
                name: 'store',
                component: () => import('../pages/Store.vue')
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
            },
            {
                path: '/products',
                name: 'products',
                component: () => import('../pages/ProductsListClient.vue')
            },
            {
                path: '/products/:id',
                name: 'product_detail',
                component: () => import('../pages/ProductDetailClient.vue')
            },
             {
                path: '/carts',
                name: 'carts',
                component: () => import('../pages/CartListClient.vue')
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
            {
                path: 'products',
                name: 'admin_products',
                component: () => import('../pages/ProductsListAdmin.vue')
            },
            {
                path: 'create_product',
                name: 'admin_create_product',
                component: () => import('../pages/CreateProduct.vue')
            },
            {
                path: 'posts',
                name: 'admin_posts',
                component: () => import('../pages/PostListAdmin.vue')
            },
            {
                path: 'add_post',
                name: 'admin_add_post',
                component: () => import('../pages/AddPostAdmin.vue')
            },
            {
                path: 'add_banner',
                name: 'admin_add_banner',
                component: () => import('../pages/AddBannerAdmin.vue')
            },
        ],
    }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;

