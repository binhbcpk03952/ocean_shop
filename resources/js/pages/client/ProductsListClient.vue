<script setup>
import { ref, onMounted,watch } from "vue";
import api from "../../axios";
import BoxProduct from "../../components/client/BoxProduct.vue";
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();
const loading = ref(false);


const products = ref([]);
const categories = ref([]);

// State để quản lý bộ lọc hiện tại
const currentFilter = ref("all");
const selectedCategoryId = ref(null); // Lưu ID danh mục đang chọn

// Hàm lấy sản phẩm linh động (nhận vào object params)
const fetchProducts = async (params = {}) => {
    try {
        loading.value = true;

        // Mặc định gọi /products, axios sẽ tự ghép params vào sau (vd: ?category_id=1)
        const response = await api.get('/products', { params });

        if (response.status === 200) {
            // Kiểm tra cấu trúc trả về (Laravel paginate thường trả về .data.data)
            products.value = response.data.data.data || response.data.data || response.data;
        }
    } catch (error) {
        console.error("Lỗi lấy danh sách sản phẩm:", error);
    } finally {
        loading.value = false;
    }
};

// Lấy danh sách danh mục để hiển thị menu
const fetchCategories = async () => {
    try {
        const res = await api.get('/categories');
        categories.value = res.data;
    } catch (error) {
        console.error("Lỗi lấy danh mục:", error);
    }
};

/* --- CÁC HÀM XỬ LÝ SỰ KIỆN CLICK --- */

// 1. Click "Tất cả"

const filterAll = () => {
    router.push({
        query: {}
    });
};

// 2. Click "Mới nhất" (Giả sử backend cần ?filter=latest hoặc ?sort=latest)
const filterLatest = () => {
    router.push({
        query: { filter: 'latest' }
    });
};

// 3. Click chọn Danh mục
const filterByCategory = (id) => {
    router.push({
        query: { category_id: id }
    });
};
const syncFromRoute = () => {
    const { category_id, filter } = route.query;

    // Reset state
    currentFilter.value = 'all';
    selectedCategoryId.value = null;

    // Danh mục
    if (category_id) {
        currentFilter.value = 'category';
        selectedCategoryId.value = Number(category_id);
        fetchProducts({ category_id });
        return;
    }

    // Mới nhất
    if (filter === 'latest') {
        currentFilter.value = 'latest';
        fetchProducts({ filter: 'latest' });
        return;
    }

    // Mặc định
    fetchProducts({});
};

watch(
    () => route.query,
    () => syncFromRoute(),
    { immediate: true }
);


onMounted(() => {
    fetchProducts(); // Mặc định load tất cả
    fetchCategories();
});
</script>

<template>
    <div class="container py-3">
        <div class="d-flex gap-2 mb-4 overflow-auto pb-2 custom-scrollbar">

            <button class="btn btn-outline-primary rounded-pill px-3 text-nowrap"
                :class="{ 'active': currentFilter === 'all' }" @click="filterAll">
                Tất cả
            </button>

            <button class="btn btn-outline-success rounded-pill px-3 text-nowrap"
                :class="{ 'active': currentFilter === 'latest' }" @click="filterLatest">
                🔥 Mới nhất
            </button>

            <button v-for="cat in categories" :key="cat.category_id"
                class="btn btn-outline-secondary rounded-pill px-3 text-nowrap"
                :class="{ 'active': selectedCategoryId === cat.category_id }"
                @click="filterByCategory(cat.category_id)">
                {{ cat.name }}
            </button>

        </div>

        <div class="row g-3">
            <div v-if="loading" class="text-center my-5">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
            <template v-else-if="products.length > 0">
            <BoxProduct v-for="product in products" :key="product.product_id" :product="product"
                class="col-6 col-md-4 col-lg-3" />
            </template>

            <div v-else class="text-center py-5 text-muted w-100">
                <i class="bi bi-inbox fs-1"></i>
                <p>Không tìm thấy sản phẩm nào trong danh mục này.</p>
            </div>
        </div>
    </div>
</template>

<style scoped>
.active {
    background-color: #0d6efd !important;
    color: #fff !important;
    border-color: #0d6efd !important;
}

/* Tùy chỉnh thanh cuộn cho đẹp khi danh sách danh mục dài */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
}

.custom-scrollbar::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 4px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #888;
}
</style>
