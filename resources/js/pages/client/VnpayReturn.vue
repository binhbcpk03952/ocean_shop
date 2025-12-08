<script setup>
import { onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../axios";

const router = useRouter();

onMounted(async () => {
    const queryString = window.location.search;
    const apiUrl = "/vnpay/return" + queryString;

    const res = await api.get(apiUrl);

    if (res.status === 200 && res.data.status) {
       console.log(res.data.data);
        router.push('/orders_success')
    } else {
        alert(res.data.message || "Thanh toán thất bại! Thanh toán lại");
        router.push('/carts')
    }
});
</script>
<template>
    <div>
        <h2>Đang xác nhận thanh toán...</h2>
    </div>
</template>
