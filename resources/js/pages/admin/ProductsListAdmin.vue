<script setup>
import { onMounted, reactive, ref, watch } from "vue";
import { useRouter, useRoute } from "vue-router";
// resources/js/pages/ProductsListAdmin.vue
// import api from "../axios";
// import ProductDetail from "../components/admin/ProductDetail.vue";
import api from "../../axios";
import ProductDetail from "../../components/admin/ProductDetail.vue";
const router = useRouter();
const route = useRoute();
const products = reactive({
    list: []
});
const categoryFillter = reactive({
    items: []
});

const initialCategory = route.query.category ? Number(route.query.category) : 0;
const selectedCate = ref(initialCategory); // Biến v-model

const formatCurrency = (value) => {
    // Thêm kiểm tra NaN để tránh lỗi
    if (isNaN(Number(value))) return '0 VND';
    return Number(value).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' })
}

const formatDate = (str) => {
    if (!str) return '';
    try {
        return new Date(str).toLocaleDateString('vi-VN');
    } catch {
        return str;
    }
}

const fetchProducts = async () => {
    try {
        const { category, search } = route.query;
        const params = new URLSearchParams();
        if (category) params.append('category', category);
        if (search) params.append('search', search);

        const url = `products${params.toString() ? '?' + params.toString() : ''}`;
        const { data, status } = await api.get(url);
        if (status === 200) {
            products.list = data.data.data || [];
            console.log(products.list)
        }
    } catch (err) {
        console.error('Error fetching products:', err);
    }
};
const category = async () => { // danh mục
    try {
        const response = await api.get(`categories`);
        // console.log(`);
        // console.log(response.data);
        categoryFillter.items = response.data;
    } catch (error) {
        console.error('Error fetching products:', error);
    }
}
const handleFillterProduct = (newCatId) => { // lọc sản phẩm theo danh mục
    const categoryId = Number(newCatId);
    const query = categoryId === 0 ? {} : { category: categoryId };

    router.push({
        name: 'admin_products',
        query: query
    });
};
const showBoxProductDetail = ref(false);
const productId = ref(0);

const handleCloseBox = () => {
    showBoxProductDetail.value = false;
}

const handleShowBoxProductDetail = (id) => {
    showBoxProductDetail.value = true;
    productId.value = id;
}

const handleSearchProduct = async () => {
    const searchInput = document.getElementById('search').value.trim();
    const query = searchInput ? { search: searchInput } : {};

    router.push({
        name: 'admin_products',
        query: query
    });
}

onMounted(() => {
    category();
    fetchProducts();
});
watch(() => route.query,
    () => {
        fetchProducts();
    }, { immediate: true });



</script>

<template>
    <ProductDetail :showBox="showBoxProductDetail" :productId="productId" @close_box="handleCloseBox" />
    <div class="">
        <div class=" mt-4">
            <h2>Quản lý sản phẩm</h2>
            <div class="option my-4 d-flex justify-content-between">
                <div class="search">
                    <label for="search fw-bold">Tìm kiếm sản phẩm: </label>
                    <form @submit.prevent="handleSearchProduct" class="d-flex">
                        <input type="text" name="search" id="search" class=" w-50"
                            placeholder="Nhập tên sản phẩm muốn tìm kiếm">
                        <button class="btn btn-primary">
                            Tìm kiếm
                        </button>
                    </form>
                </div>
                <div class="add mt-3">
                    <router-link to="/admin/create_product" class="btn btn-primary">Thêm sản phẩm</router-link>
                </div>
            </div>
            <div class="filter">
                <label for="filter" class="me-2">Lọc sản phẩm</label>
                <select id="filter" class="p-1" v-model="selectedCate" @change="handleFillterProduct(selectedCate)">
                    <option value="0" selected>Tất cả</option>
                    <template v-for="cat in categoryFillter.items" :key="cat.category_id">
                        <option :value="cat.category_id">{{ cat.name }}</option>

                        <template v-if="cat.children && cat.children.length > 0">
                            <option v-for="child in cat.children" :key="child.category_id" :value="child.category_id">
                                — {{ child.name }}
                            </option>
                        </template>
                    </template>
                </select>
            </div>
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:50px">#</th>
                        <th style="width: 300px;">Tên sản phẩm</th>
                        <th style="width:190px" class="text-end">Giá</th>
                        <th style="width:200px" class="text-center">Danh mục</th>
                        <th style="width:200px">Ngày thêm</th>
                        <th></th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(prod, index) in products.list" :key="prod.product_id" v-if="products.list.length > 0">
                        <td>{{ index + 1 }}</td>
                        <td>{{ prod.name }}</td>
                        <td class="text-end">{{ formatCurrency(prod.price) }}</td>
                        <td class="text-center">
                            <span class="bg-success text-white rounded px-1 small" v-if="prod.categories">
                                {{ prod.categories.name }}
                            </span>
                            <span v-else>Chưa có danh mục</span>
                        </td>
                        <td>{{ formatDate(prod.created_at) }}</td>
                        <td>
                            <span class="text-primary small text-decoration-underline" style="cursor: pointer;"
                                @click="handleShowBoxProductDetail(prod.product_id)">
                                Xem chi tiết
                            </span>
                        </td>
                        <td class="text-center">
                            <router-link :to="'/admin/edit_product/' + prod.product_id" :key="prod.product_id"
                                class="btn btn-sm btn-outline-primary">Edit</router-link>
                            <button class="btn btn-sm btn-outline-danger ms-1">
                                Delete
                            </button>
                        </td>
                    </tr>
                    <tr v-else>
                        <td colspan="7">
                            <div class="alert alert-info text-center w-100 py-3">
                                Không có sản phẩm nào trong danh mục này.
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
#search {
    border-radius: 5px;
    border: 1px solid black;
    padding: 0 10px;
}

#search:focus {
    outline: none;
    border: 1px solid;
}

#filter {
    width: 180px;
    border: 1px solid #000;
    border-radius: 3px;
}

#filter:focus {
    outline: none;
    border: 1px solid #007b23;
}
</style>
