<script setup>
    import { onMounted, provide } from "vue";
    // 🔑 IMPORT STATE VÀ HÀM TỪ FILE JS MỚI
    import { authState, checkLogin } from '@/stores/authGlobal.js';

    onMounted(() => {
        checkLogin();
    })
    provide('auth', {
        auth: authState, // Sử dụng authState đã import
        checkLogin
    })
</script>
<template>
    <slot></slot>
</template>
