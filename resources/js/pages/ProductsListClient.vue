<script setup>
// products list
import { ref, watch, reactive, onMounted } from 'vue';
import api from '../axios';
import BoxProduct from '../components/client/BoxProduct.vue';
const products = ref([])
const handleFetchProducts = async () => {
    try {
        const response = await api.get('/products')
        if (response.status === 200) {
            products.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách sản phẩm:', error)
    }
}
onMounted(() => {
    handleFetchProducts()
    
})
</script>

<template>
    <div class="container">
        <div class="breadcrumb" style="font-size: 15px;">
            <router-link class="text-secondary me-1">Trang chủ /</router-link> Tên danh mục
        </div>
        <div class="fillter-products">
            <button class="btn-custom">Ưu đãi</button>
            <button class="btn-custom">Mới nhất</button>

            <button>
                <i class="bi bi-funnel "></i>
                Lọc
            </button>
        </div>
        <div class="row">
            <BoxProduct v-for="product in products" :key="product.product_id" :product="product" />
        </div>
    </div>
</template>
<style scoped>
.btn-custom {
    border: none;
    background-color: transparent;
    font-weight: 400;
}
</style>
