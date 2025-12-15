<script setup>

import { ref, onMounted } from 'vue';
import api from '../../axios';


const dashboardData = ref({})

const fetchDashboardData = async () => {
    try {
        const res = await api.get('/dashboard');
        dashboardData.value = res.data;
    } catch (err) {
        console.log('Lỗi khi gọi API: ', err);
    }
};

onMounted(() => {
    fetchDashboardData();
});
</script>

<template>
    <div class="col-lg-3">
        <div class="card bg-success text-white p-4 border-0">
            <div class="fs-3 fw-medium stock">{{ dashboardData.totalOrder }}</div>
            <div class="title">
                Đơn hàng mới
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card bg-danger text-white p-4 border-0">
            <div class="fs-3 fw-medium stock">5</div>
            <div class="title">
                Lượt thích
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card bg-primary text-white p-4 border-0">
            <div class="fs-3 fw-medium stock">{{ dashboardData.totalUser }}</div>
            <div class="title">
                Khách hàng
            </div>
        </div>
    </div>
    <div class="col-lg-3">
        <div class="card bg-warning text-white p-4 border-0">
            <div class="fs-3 fw-medium stock">{{ dashboardData.totalProduct?.length }}</div>
            <div class="title">
                Sản phẩm bán chạy
            </div>
        </div>
    </div>
</template>
