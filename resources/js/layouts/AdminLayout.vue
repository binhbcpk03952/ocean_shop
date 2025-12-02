<script setup>
import { useRouter } from 'vue-router';
import AsideLayoutAdmin from '../components/admin/AsideLayoutAdmin.vue';
import HeaderLayoutAdmin from '../components/admin/HeaderLayoutAdmin.vue';
const router = useRouter();
</script>
<template>
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-2 ps-5 position-sticky top-0"   style="background-color: #0A3D62; height: 100vh;">
                <aside-layout-admin></aside-layout-admin>
            </div>
            <div class="col-lg-10 pe-5">
                <header-layout-admin></header-layout-admin>
                <router-view />
            </div>
        </div>
    </div>
</template>
