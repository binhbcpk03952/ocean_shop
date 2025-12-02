<script setup>
import { onMounted, computed, ref, watch, defineEmits, defineProps } from 'vue';
import axios from 'axios';
import api from '../../axios';

const HOST = import.meta.env.VITE_URL_API;

const props = defineProps({
    showBox: Boolean,
    productId: Number,
});

// Chuyển sang ref để dễ dàng gán trực tiếp kết quả API (là một đối tượng)
const productDetail = ref(null);
const formatCurrency = (value) => {
    if (isNaN(Number(value)) || value === null) return '0 VND';
    const cleanValue = String(value).replace(/\./g, '');
    return Number(cleanValue).toLocaleString('vi-VN', { style: 'currency', currency: 'VND' });
};

const handleFetchProductDetailById = async (productId) => {
    if (!productId) {
        productDetail.value = null;
        return;
    }

    productDetail.value = null; // Đặt về null khi tải sản phẩm mới
    try {
        // API trả về sản phẩm và các mảng biến thể/hình ảnh lồng nhau
        const res = await api.get(`products/${productId}`);
        if (res.status === 200) {
            productDetail.value = res.data;
        } else {
            productDetail.value = null;
        }
    } catch (err) {
        console.error("Lỗi khi tải chi tiết sản phẩm:", err);
        productDetail.value = null;
    }
};

const uniquedImages = computed(() => {
    if (!productDetail.value || !productDetail.value.image) return [];
    const seen = new Set();
    return productDetail.value.image.filter(img => {
        const duplicate = seen.has(img.image_url);
        seen.add(img.image_url);
        return !duplicate;
    });
});
// Hình ảnh chính để hiển thị
const mainImage = computed(() => {
    if (!productDetail.value || !productDetail.value.image) return null;
    return productDetail.value.image.find(img => img.is_main === 1) || productDetail.value.image[0];
});

const currentDisplayImage = ref(null);

watch(() => props.productId, (newId) => {
    handleFetchProductDetailById(newId);
}, { immediate: true });

const emit = defineEmits(['close_box'])

const handleCloseBox = () => {
    emit('close_box')
}

watch(mainImage, (newMainImage) => {
    if (newMainImage) {
        currentDisplayImage.value = newMainImage.image_url;
    }
}, { immediate: true });
const handleThumbnailClick = (imageObject) => {
    // Cập nhật biến trạng thái với đối tượng ảnh được nhấp
    currentDisplayImage.value = imageObject.image_url;
};

</script>

<template>
    <div class="modal fade show d-block" v-if="props.showBox" tabindex="-1"
        style="background-color: rgb(0, 0, 0, 0.3);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3 p-4 pt-2">
                <div class="modal-header">
                    <h5 class="modal-title text-primary fw-bold">
                        <i class="bi bi-eye me-2"></i> Chi tiết sản phẩm (Admin)
                    </h5>
                    <button type="button" class="btn-close" @click="handleCloseBox"></button>
                </div>

                <div class="modal-body p-0" v-if="productDetail">
                    <div class="row">
                        <div class="col-md-5 my-3">
                            <div class="main-img border rounded mb-2 p-2 text-center">
                                <img :src="`../../../../storage/${currentDisplayImage}`" :alt="productDetail.name"
                                    class="img-fluid rounded-1" style="max-height: 350px;" alt="Thumbnail Image">
                            </div>
                            <div class="thumbnail-list d-flex justify-content-start gap-2 overflow-auto">
                                <img v-for="img in uniquedImages" :key="img.image_id"
                                    :src="`../../../../storage/${img.image_url}`" class="border img-thumbnail rounded-1"
                                    style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;"
                                    alt="Thumbnail Image" @click="handleThumbnailClick(img)" :class="['img-fluid border rounded-3', {
                                        'border-primary border': img.image_url === currentDisplayImage // Hiệu ứng ảnh đang chọn
                                    }]">
                            </div>
                        </div>

                        <div class="col-md-7 mt-3">
                            <h4 class="text-dark fw-bold mb-2">{{ productDetail.name }}</h4>

                            <div class="price mb-3">
                                <span class="text-danger fw-bolder fs-3">
                                    Giá: {{ formatCurrency(productDetail.price) }}
                                </span>
                            </div>

                            <p class="mb-1">
                                <strong>ID Sản phẩm:</strong>
                                <span class="text-secondary">{{ productDetail.product_id }}</span>
                            </p>
                            <p class="mb-1">
                                <strong>ID Danh mục:</strong>
                                <span class="text-secondary">{{ productDetail.category_id }}</span>
                            </p>
                            <p class="mb-3">
                                <strong>Ngày tạo:</strong>
                                <span class="text-secondary">{{ productDetail.created_at }}</span>
                            </p>

                            <h6 class="fw-bold">Mô tả:</h6>
                            <p class="small text-muted mb-3">{{ productDetail.description }}</p>

                            <h6 class="fw-bold text-primary mb-2">Chi tiết Biến thể
                                ({{ productDetail.variant.length }})
                            </h6>
                            <div class="variant-list border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <table class="table table-sm mb-0">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Màu</th>
                                            <th>Size</th>
                                            <th class="text-end">Giá</th>
                                            <th class="text-end">Tồn kho</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="v in productDetail.variant" :key="v.variant_id"
                                            :class="{ 'text-muted': v.stock == 0 }">
                                            <td>{{ v.variant_id }}</td>
                                            <td>
                                                <div class="box-color" :style="{ backgroundColor: v.color }"></div>
                                            </td>
                                            <td>{{ v.size }}</td>
                                            <td class="text-end">{{ formatCurrency(v.price) }}</td>
                                            <td class="text-end">
                                                <span class="fw-bold"
                                                    :class="{ 'text-danger': v.stock == 0, 'text-success': v.stock > 0 }">
                                                    {{ v.stock }}
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="modal-body p-4 text-center" v-else>
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Đang tải chi tiết sản phẩm...</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* (Giữ nguyên các styles scoped bạn đã cung cấp) */
.box-color {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    border: 1px solid #3497E0;
}
</style>
