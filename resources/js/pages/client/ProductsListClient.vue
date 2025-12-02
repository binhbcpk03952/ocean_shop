<script setup>
// products list
import { ref, watch, reactive, onMounted } from 'vue';
import api from '../../axios';
import BoxProduct from '../../components/client/BoxProduct.vue';
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
    <div class="container py-3">

        <!-- Breadcrumb đẹp hơn -->
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <router-link to="/" class="text-decoration-none text-secondary">Trang chủ</router-link>
                </li>
                <li class="breadcrumb-item active" aria-current="page">Tên danh mục</li>
            </ol>
        </nav>

        <!-- FILTER -->
        <div class="d-flex gap-2 mb-3">

            <button class="btn btn-outline-primary rounded-pill px-3">
                Ưu đãi
            </button>

            <button class="btn btn-outline-success rounded-pill px-3">
                Mới nhất
            </button>

            <button class="btn btn-primary rounded-pill px-3 d-flex align-items-center gap-1">
                <i class="bi bi-funnel"></i>
                Lọc
            </button>
        </div>

        <!-- PRODUCTS -->
        <div class="row g-3">
            <BoxProduct
                v-for="product in products"
                :key="product.product_id"
                :product="product"
                class="col-6 col-md-4 col-lg-3"
            />
        </div>
    </div>
</template>

<style scoped>
/* Điều chỉnh nút filter đẹp hơn */
.btn-custom {
    border: none;
    background-color: transparent;
    font-weight: 400;
}

.breadcrumb {
    font-size: 15px;
}
</style>
