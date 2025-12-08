import { createRouter, createWebHistory } from "vue-router";
import AdminLayout from "../layouts/AdminLayout.vue";
import ClientLayout from "../layouts/ClientLayout.vue";
import ProfileLayout from "../layouts/ProfileLayout.vue";
const routes = [
    {
        path: '/',
        component: ClientLayout,
        children: [
            {
                path: '',
                name: 'home',
                component: () => import('../pages/client/HomeView.vue')
            },
            // San pham
            {
                path: '/product',
                name: 'product',
                component: () => import('../pages/client/Product.vue')
            },
            // Tin tuc
            {
                path: '/blog',
                name: 'blog',
                component: () => import('../pages/client/Blog.vue')
            },
            //
            {
                path: '/blog/:id',
                name: 'blog_detail',
                component: () => import('../pages/client/BlogDetail.vue')
            },
            // Store
            {
                path: '/store',
                name: 'store',
                component: () => import('../pages/client/Store.vue')
            },
            {
                path: '/login',
                name: 'login',
                component: () => import('../pages/client/LoginAdmin.vue')
            },
            {
                path: '/register',
                name: 'register',
                // component: () => import('../pages/Register.vue')
                component: () => import('../pages/client/Register.vue')
            },
            {
                path: '/products',
                name: 'products',
                // component: () => import('../pages/ProductsListClient.vue')
                component: () => import('../pages/client/ProductsListClient.vue')
            },
            {
                path: '/products/:id',
                name: 'product_detail',
                // component: () => import('../pages/ProductDetailClient.vue')
                component: () => import('../pages/client/ProductDetailClient.vue')
            },
            {
                path: '/carts',
                name: 'carts',
                // component: () => import('../pages/CartListClient.vue')
                component: () => import('../pages/client/CartListClient.vue')
            },
            {
                path: '/checkout',
                name: 'checkout',
                component: () => import('../pages/client/Checkout.vue')
            },
            {
                path: '/profile',
                component: ProfileLayout,
                children: [
                    {
                        path: '',
                        name: 'profile',
                        component: () => import('../pages/client/Profile.vue')
                    },
                    {
                        path: 'address',
                        name: 'address',
                        component: () => import('../pages/client/Address.vue')
                    },
                ]
            },

            {
                path: 'create_address',
                name: 'create_address',
                component: () => import('../pages/client/CreateAddress.vue')
            },
            {
                path: 'orders_success',
                name: 'orders_success',
                component: () => import('../pages/client/OrderSuccess.vue')
            },
            {
                path: 'vnpay_return',
                name: 'vnpay_return',
                component: () => import('../pages/client/VnpayReturn.vue')
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
                component: () => import('../pages/admin/DashboardAdmin.vue')
            },
            {
                path: 'categories',
                name: 'admin_categories',
                component: () => import('../pages/admin/CategoriesListAdmin.vue')
            },
            {
                path: 'products',
                name: 'admin_products',
                component: () => import('../pages/admin/ProductsListAdmin.vue')
            },
            {
                path: 'create_product',
                name: 'admin_create_product',
                component: () => import('../pages/admin/CreateProduct.vue')
            },
            {
                path: 'edit_product/:id',
                name: 'admin_edit_product',
                component: () => import('../pages/admin/EditProduct.vue')
            },
            {
                path: 'posts',
                name: 'admin_posts',
                component: () => import('../pages/admin/PostListAdmin.vue')
            },
            {
                path: 'add_post',
                name: 'admin_add_post',
                component: () => import('../pages/admin/AddPostAdmin.vue')
            },
            {
                path: 'banners',
                name: 'admin_banners',
                component: () => import('../pages/admin/BannerListAdmin.vue')
            },
            {
                path: 'add_banner',
                name: 'admin_add_banner',
                component: () => import('../pages/admin/AddBannerAdmin.vue')
            },
            {
                path: 'users',
                name: 'admin_users',
                component: () => import('../pages/admin/UserListAdmin.vue')
            },
            {
                path: 'orders',
                name: 'admin_orders',
                component: () => import('../pages/admin/OrderListAdmin.vue')
            }
        ],
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;

