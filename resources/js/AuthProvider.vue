<script setup>
import { provide, onMounted } from 'vue';
import { authState, checkLogin } from '@/stores/authGlobal.js';

// Khi component gốc mounted, check login
onMounted(async () => {
  await checkLogin();
});

// Provide state và hàm cho toàn app
provide('auth', {
  auth: authState,
  checkLogin
});
</script>

<template>
  <slot></slot>
</template>
