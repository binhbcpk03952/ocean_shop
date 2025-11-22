<script setup>
import { useRoute } from 'vue-router';
const route = useRoute();
const isActive = (path) => {
    return route.path === path
}
</script>
<template>
    <aside>
        <div class="logo mt-2">
            <img src="../../../../public/images/logo_ocean.png" alt="logo ocean" width="160px">
        </div>
        <div class="navigation">
            <ul class="ps-1">
                <li class="mt-4">
                    <router-link to="/admin"  class="text-white text-decoration-none" :class="{ activepage: isActive('/admin') }">Dashboard</router-link>
                </li>
                <li class="mt-4">
                    <router-link to="/admin/products"  class="text-white text-decoration-none" :class="{ activepage: isActive('/admin/products') }">Sản phẩm</router-link>
                </li>
                <li class="mt-4">Thương hiệu</li>
                <li class="mt-4">
                    <router-link to="/admin/categories" class="text-white text-decoration-none" :class="{ activepage: isActive('/admin/categories') }">Danh
                        mục</router-link>
                </li>
                <li class="mt-4">Voucher</li>
                <li class="mt-4">
                   <router-link to="/admin/posts" class="text-white text-decoration-none" :class="{ activepage: isActive('/admin/categories') }">Bài viết</router-link>
                </li>
                <li class="mt-4">Banner</li>
                <li class="mt-4">Hoá đơn</li>
                <li class="mt-4">Tài khoản</li>
                <li class="mt-4">Thống kê</li>
                <li class="mt-4">Danh sách đen</li>
            </ul>
        </div>
    </aside>
</template>
<style>
.navigation>ul>li {
    color: white;
    font-size: 1rem;
    list-style: none;
}

.activepage {
    color: #3497E0 !important;
}
</style>
