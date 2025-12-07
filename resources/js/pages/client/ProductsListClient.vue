<script setup>
import { ref, onMounted } from "vue";
import api from "../../axios";
import BoxProduct from "../../components/client/BoxProduct.vue";

const products = ref([]);
const currentFilter = ref("all");

const fetchProducts = async (filter = "all") => {
    try {
        let url = "/products";

        if (filter === "sale") url = "/products?filter=sale";
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

const filters = ref({
    price_range: "",
    category_id: "",
    size: "",
});

const categories = ref([]);
const sizes = ref([]);

// Lấy categories từ API
const fetchCategories = async () => {
    const res = await api.get('/categories');
    categories.value = res.data;
};

// Tự lấy size từ product
const fetchSizes = async () => {
    const res = await api.get('/products');
    const list = res.data.flatMap(p => p.variant.map(v => v.size));
    sizes.value = [...new Set(list)]; // unique
};

const applyFilters = async () => {
    let url = '/products?';

    if (filters.value.price_range)
        url += `price_range=${filters.value.price_range}&`;

    if (filters.value.category_id)
        url += `category_id=${filters.value.category_id}&`;

    if (filters.value.size)
        url += `size=${filters.value.size}&`;

    const res = await api.get(url);
    products.value = res.data;
};

onMounted(() => {
    fetchProducts();       // load tất cả sản phẩm
    fetchCategories();     // load danh mục
    fetchSizes();          // load size
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

            <!-- Modal -->
            <div class="modal fade" id="filterModal" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content p-3">
                        <h5 class="fw-bold mb-3">Bộ lọc</h5>

                        <!-- Lọc Giá -->
                        <div class="mb-3">
                            <label class="fw-bold">Giá</label>
                            <select v-model="filters.price_range" class="form-select">
                                <option value="">Tất cả</option>
                                <option value="under_100">Dưới 100k</option>
                                <option value="100_1m">100k - 1 triệu</option>
                                <option value="1m_5m">1 triệu - 5 triệu</option>
                                <option value="over_5m">Trên 5 triệu</option>
                            </select>
                        </div>

                        <!-- Lọc Danh mục -->
                        <div class="mb-3">
                            <label class="fw-bold">Danh mục</label>
                            <select v-model="filters.category_id" class="form-select">
                                <option value="">Tất cả</option>
                                <option v-for="cate in categories" :value="cate.category_id">
                                    {{ cate.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Lọc Size -->
                        <div class="mb-3">
                            <label class="fw-bold">Size</label>
                            <select v-model="filters.size" class="form-select">
                                <option value="">Tất cả</option>
                                <option v-for="s in sizes" :value="s">{{ s }}</option>
                            </select>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                            <button class="btn btn-primary" @click="applyFilters">Áp dụng</button>
                        </div>

                    </div>
                </div>
            </div>

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
