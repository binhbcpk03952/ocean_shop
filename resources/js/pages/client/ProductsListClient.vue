<script setup>
import { ref, onMounted } from "vue";
import api from "../../axios";
import BoxProduct from "../../components/client/BoxProduct.vue";

const products = ref([]);
const currentFilter = ref("all");

const fetchProducts = async (filter = "all") => {
    try {
        let url = "/products";
        if (filter === "latest") url = "/products?filter=latest";

        const response = await api.get(url);

        if (response.status === 200) {
            products.value = response.data;
            currentFilter.value = filter;
        }
    } catch (error) {
        console.error("Lỗi lấy danh sách sản phẩm:", error);
    }
};

const categories = ref([]);
// Lấy categories từ API
const fetchCategories = async () => {
    const res = await api.get('/categories');
    categories.value = res.data;
};

onMounted(() => {
    fetchProducts();       // load tất cả sản phẩm
    fetchCategories();     // load danh mục
});
</script>

<template>
    <div class="container py-3">
        <!-- FILTER -->
        <div class="d-flex gap-2 mb-3">

            <!-- BUTTON TẤT CẢ -->
            <button class="btn btn-outline-primary rounded-pill px-3" :class="currentFilter === 'all' ? 'active' : ''"
                @click="fetchProducts('all')">
                Tất cả
            </button>

            <!-- BUTTON ƯU ĐÃI -->
            <!-- <button
                class="btn btn-outline-primary rounded-pill px-3"
                :class="currentFilter === 'sale' ? 'active' : ''"
                @click="fetchProducts('sale')"
            >
                Ưu đãi
            </button> -->

            <!-- BUTTON MỚI NHẤT -->
            <button class="btn btn-outline-success rounded-pill px-3"
                :class="currentFilter === 'latest' ? 'active' : ''" @click="fetchProducts('latest')">
                Mới nhất
            </button>

            <!-- Button mở modal -->
            <button class="btn btn-outline-primary rounded-pill px-3 d-flex align-items-center gap-1" data-bs-toggle="modal"
                data-bs-target="#filterModal">
                <i class="bi bi-funnel"></i> Lọc
            </button>
        </div>

        <!-- PRODUCTS -->
        <div class="row g-3">
            <BoxProduct v-for="product in products" :key="product.product_id" :product="product"
                class="col-6 col-md-4 col-lg-3" />
        </div>
    </div>
</template>

<style scoped>
.active {
    background-color: #0d6efd !important;
    color: #fff !important;
}
</style>
