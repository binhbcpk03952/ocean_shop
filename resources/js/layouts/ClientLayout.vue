<script setup>
import HeaderClient from '../components/client/HeaderClient.vue';
import FooterLayoutClient from '../components/client/FooterLayoutClient.vue';
</script>
<template>
    <div class="px-0 container-custom-pad possition-relative">
        <HeaderClient />
        <main>
            <router-view />
        </main>
        <FooterLayoutClient />
    </div>
</template>
