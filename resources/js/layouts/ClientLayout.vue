<script setup>
import HeaderClient from '../components/client/HeaderClient.vue';
import FooterLayoutClient from '../components/client/FooterLayoutClient.vue';
</script>
<template>
    <div class="container px-0 container-custom-pad possition-relative">
        <HeaderClient />
        <main>
            <router-view />
        </main>
    </div>
    <div class="container-fluid px-0">
            <FooterLayoutClient/>
    </div>
</template>
