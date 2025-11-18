<script setup>
import { reactive, onMounted, ref, toRaw } from 'vue';
// import axios from 'axios';
import api from '../axios';
import { useRouter } from 'vue-router';
import CategoryFormItem from '../components/admin/CategoryFormItem.vue';
const HOST = import.meta.env.VITE_URL_API

const router = useRouter();
const categories = reactive({ items: [] });

const handleFetchCategories = async () => {
    const res = await api.get('/categories');
    if (res.status === 200) {
        categories.items = res.data;
    } else {
        console.log('Không thể gọi API');
    }
};

onMounted(() => {
    handleFetchCategories();
});

const error = ref({});

const validateForm = () => {
    error.value = {};
    let isValid = true;

    if (!product.nameProduct) {
        error.value.nameProduct = 'Tên sản phẩm là bắt buộc.';
        isValid = false;
    }
    if (!product.price || product.price <= 0) {
        error.value.price = 'Giá sản phẩm phải lớn hơn 0.';
        isValid = false;
    }
    if (!product.category_id) {
        error.value.category_id = 'Vui lòng chọn danh mục sản phẩm.';
        isValid = false;
    }

    return isValid;
}

const product = reactive({
    nameProduct: '',
    price: 0,
    description: '',
    category_id: null,
    images: [],
    imagePreviews: [],
    variants: [
        {
            color: '#000000',
            images: [], imagePreview: [],
            sizes: [{ size: '', stock: 0, price: 0 }],
        },
    ],
});

const handleGeneralImageUpload = (event) => {
    const files = Array.from(event.target.files);
    product.images = files;
    product.imagePreviews = files.map((file) => URL.createObjectURL(file));
};

const handleVariantImageUpload = (event, vIndex) => {
    const files = Array.from(event.target.files);
    product.variants[vIndex].images = files;
    product.variants[vIndex].imagePreview = files.map(file => URL.createObjectURL(file));
}

const handleCategorySelected = (id) => {
    product.category_id = id;
}

const handleAddProduct = async () => {
    if (!validateForm()) {
        return;
    }
    const formData = new FormData();
    formData.append('name', product.nameProduct);
    formData.append('price', product.price);
    formData.append('description', product.description);
    formData.append('category_id', product.category_id);

    // Thêm ảnh giới thiệu chung
    product.images.forEach((img) => formData.append('images[]', img));

    // Chuẩn bị dữ liệu biến thể và hình ảnh
    const variantsData = toRaw(product.variants).map(variant => ({
        color: variant.color,
        sizes: variant.sizes,
    }));
    formData.append('variants', JSON.stringify(variantsData));

    product.variants.forEach((variant, index) => {
        variant.images.forEach(imageFile => {
            formData.append(`variant_images[${index}][]`, imageFile);
        });
    });

    console.log('Dữ liệu FormData đã chuẩn bị để gửi đi:');
    for (let [key, value] of formData.entries()) {
        console.log(`- ${key}:`, value);
    }


    try {
        const response = await api.post(`/products`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        if (response.data.status === true) {
            alert(response.data.message);
            router.push('/admin/products');
        } else {
            console.log(response.data.message);
        }
    } catch (err) {
        console.error('Lỗi khi gọi API: ' + err);
    }
};
</script>

<template>
    <div class="container mt-4 mb-5">
        <h3 class="fw-bold text-primary mb-4">
            <i class="bi bi-plus-circle me-2"></i> Thêm sản phẩm mới
        </h3>

        <form @submit.prevent="handleAddProduct" class="row g-4">
            <!-- THÔNG TIN CƠ BẢN -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-primary text-white fw-semibold">
                        <i class="bi bi-info-circle me-1"></i> Thông tin cơ bản
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Tên sản phẩm</label>
                            <input v-model="product.nameProduct" type="text" class="form-control"
                                placeholder="Nhập tên sản phẩm" />
                            <small v-if="error.nameProduct" class="text-danger">{{ error.nameProduct }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Giá sản phẩm (₫)</label>
                            <input v-model="product.price" type="number" class="form-control"
                                placeholder="Nhập giá sản phẩm" />
                            <small v-if="error.price" class="text-danger">{{ error.price }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Mô tả sản phẩm</label>
                            <textarea v-model="product.description" class="form-control" rows="4"
                                placeholder="Nhập mô tả chi tiết..."></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Danh mục sản phẩm</label>
                            <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                                <ul class="list-unstyled mb-0">
                                    <CategoryFormItem v-for="cat in categories.items" :key="cat.category_id" :cat="cat"
                                        :level="0" :current-parent-id="categories.parent"
                                        @update:parent="handleCategorySelected" />
                                </ul>
                            </div>
                            <small v-if="error.category_id" class="text-danger">{{ error.category_id }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- HÌNH ẢNH & BIẾN THỂ -->
            <div class="col-md-6">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-success text-white fw-semibold">
                        <i class="bi bi-images me-1"></i> Hình ảnh giới thiệu
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Những hình ảnh này sẽ được dùng làm ảnh đại diện, ảnh thumbnail cho
                            sản phẩm.</p>
                        <input type="file" multiple class="form-control mb-3" @change="handleGeneralImageUpload" />
                        <div v-if="product.imagePreviews.length" class="d-flex flex-wrap gap-2">
                            <img v-for="(src, i) in product.imagePreviews" :key="i" :src="src" class="rounded shadow-sm"
                                style="width: 100px; height: 100px; object-fit: cover;" />
                        </div>
                    </div>
                </div>

                <div class="card shadow-sm border-0">
                    <div class="card-header bg-warning fw-semibold">
                        <i class="bi bi-palette me-1"></i> Biến thể sản phẩm
                    </div>
                    <div class="card-body">
                        <div v-for="(variant, vIndex) in product.variants" :key="vIndex"
                            class="mb-3 p-3 border rounded bg-light position-relative">
                            <button type="button" class="btn-close position-absolute top-0 end-0 m-2"
                                v-if="product.variants.length > 1" @click="product.variants.splice(vIndex, 1)"
                                aria-label="Close"></button>

                            <div class="row g-3">
                                <!-- Input màu sắc và hình ảnh -->
                                <div class="col-12">
                                    <label class="form-label fw-medium">Màu sắc & Hình ảnh biến thể</label>
                                    <div class="d-flex align-items-center">
                                        <input type="color" v-model="variant.color"
                                            class="form-control form-control-color me-3" title="Chọn màu">
                                        <input type="file" multiple class="form-control"
                                            @change="handleVariantImageUpload($event, vIndex)">
                                    </div>
                                    <!-- Xem trước ảnh biến thể -->
                                    <div v-if="variant.imagePreview.length" class="mt-2 d-flex flex-wrap gap-2">
                                        <img v-for="(src, i) in variant.imagePreview" :key="i" :src="src"
                                            class="rounded shadow-sm"
                                            style="width: 60px; height: 60px; object-fit: cover;" />
                                    </div>
                                </div>

                                <!-- Bảng size -->
                                <div class="col-12">
                                    <table class="table table-sm align-middle text-center table-bordered">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Kích thước</th>
                                                <th>Số lượng</th>
                                                <th>Giá (để trống nếu dùng giá chính)</th>
                                                <th><i class="bi bi-trash"></i></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(size, sIndex) in variant.sizes" :key="sIndex">
                                                <td><input type="text" v-model="size.size"
                                                        class="form-control form-control-sm" placeholder="M, L, XL" />
                                                </td>
                                                <td><input type="number" v-model="size.stock"
                                                        class="form-control form-control-sm" placeholder="Tồn kho" />
                                                </td>
                                                <td><input type="number" v-model="size.price"
                                                        class="form-control form-control-sm" placeholder="Giá riêng" />
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-outline-danger btn-sm border-0"
                                                        @click="variant.sizes.splice(sIndex, 1)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                    <button type="button" class="btn btn-outline-primary btn-sm"
                                        @click="variant.sizes.push({ size: '', stock: 0, price: 0 })">
                                        <i class="bi bi-plus-lg me-1"></i> Thêm kích thước
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="button" class="btn btn-success btn-sm"
                                @click="product.variants.push({ color: '', images: [], imagePreview: [], sizes: [{ size: '', stock: 0, price: 0 }] })">
                                <i class="bi bi-plus-circle me-1"></i> Thêm biến thể mới
                            </button>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-lg btn-primary px-4">
                        <i class="bi bi-save me-1"></i> Lưu sản phẩm
                    </button>
                </div>
            </div>
        </form>
    </div>
</template>

<style scoped>
.card-header {
    font-size: 1rem;
}
</style>
