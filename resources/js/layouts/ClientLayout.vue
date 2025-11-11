<script setup>
import HeaderClient from '../components/client/HeaderClient.vue';
</script>
<template>
    <div class="container px-0 container-custom-pad possition-relative">
        <HeaderClient />
        <main>
            <router-view />
        </main>
    </div>
</template>
