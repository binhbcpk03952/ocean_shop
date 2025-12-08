<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../axios';

const props = defineProps({
    openModal: Boolean,
    productId: { type: Number, required: true },
    color: { type: String, default: null },
    size: { type: String, default: null }
})

const productDetail = ref(null)
const isLoading = ref(false); // Thêm trạng thái loading

// 1. Fetch dữ liệu
const handleFetchProductDetailById = async (id) => {
    isLoading.value = true;
    try {
        const res = await api.get(`products/${id}`);
        if (res.status === 200) {
            productDetail.value = res.data;
            // Sau khi có dữ liệu, thiết lập giá trị mặc định ngay lập tức
            initSelection();
        }
    } catch (err) {
        console.error("Lỗi:", err);
    } finally {
        isLoading.value = false;
    }
}

// 2. Màu sắc & Size
const selectedColor = ref(null);
const selectedSize = ref(null);

const handleChangeColor = (color) => {
    selectedColor.value = color;
    // Khi đổi màu, tự động chọn size đầu tiên của màu đó để UX tốt hơn
    if (uniqueSizes.value.length > 0) {
        selectedSize.value = uniqueSizes.value[0].size;
    }
}

const handleChangeSize = (size) => {
    selectedSize.value = size;
}

// 3. Computed Variants
const uniqueVariants = computed(() => {
    if (!productDetail.value?.variant) return [];
    const seen = new Set();
    return productDetail.value.variant.filter(variant => {
        const duplicate = seen.has(variant.color);
        seen.add(variant.color);
        return !duplicate;
    });
});

const uniqueSizes = computed(() => {
    if (!productDetail.value?.variant || !selectedColor.value) return [];
    const seen = new Set();
    return productDetail.value.variant
        .filter(variant => variant.color === selectedColor.value)
        .filter(variant => {
            const duplicate = seen.has(variant.size);
            seen.add(variant.size);
            return !duplicate;
        });
})

// 4. Logic khởi tạo lựa chọn (QUAN TRỌNG)
const initSelection = () => {
    if (!productDetail.value) return;

    // Ưu tiên props.color (màu hiện tại của cart), nếu không có thì lấy màu đầu tiên
    if (props.color) {
        selectedColor.value = props.color;
    } else if (uniqueVariants.value.length > 0) {
        selectedColor.value = uniqueVariants.value[0].color;
    }

    // Ưu tiên props.size, nếu không khớp với màu hiện tại thì lấy size đầu tiên khả dụng
    if (props.size) {
        // Kiểm tra xem size này có tồn tại trong màu đã chọn không
        const isValidSize = productDetail.value.variant.some(
            v => v.color === selectedColor.value && v.size === props.size
        );
        selectedSize.value = isValidSize ? props.size : (uniqueSizes.value[0]?.size || null);
    } else {
        selectedSize.value = uniqueSizes.value[0]?.size || null;
    }
}

// 5. Watchers
// Khi mở modal hoặc đổi productId -> Reset và Fetch lại
watch(() => props.openModal, (isOpen) => {
    if (isOpen && props.productId) {
        // Reset state cũ để tránh hiển thị sai trong lúc chờ load
        productDetail.value = null;
        selectedColor.value = null;
        selectedSize.value = null;
        handleFetchProductDetailById(props.productId);
    }
});

const imageWithVariant = computed(() => {
    if (!productDetail.value?.image) return null; // Safe check

    if (selectedColor.value) {
        const targetVariant = productDetail.value.variant.find(v => v.color === selectedColor.value && v.size === selectedSize.value);
        // Nếu không tìm thấy chính xác variant (do chưa chọn size), tìm theo màu
        const variantByColor = productDetail.value.variant.find(v => v.color === selectedColor.value);
        const idToFind = targetVariant?.variant_id || variantByColor?.variant_id;

        if (idToFind) {
            const targetImage = productDetail.value.image.find(img => img.variant_id === idToFind);
            if (targetImage) return targetImage.image_url;
        }
    }
    const mainImage = productDetail.value.image.find(img => img.is_main === 1);
    return mainImage?.image_url || productDetail.value.image[0]?.image_url;
});

const emit = defineEmits(['close', 'save'])
const closeModal = () => emit('close')

const handleChangeVariant = () => {
    if (!selectedColor.value || !selectedSize.value) {
        alert("Vui lòng chọn màu và kích thước");
        return;
    }
    const newVariant = productDetail.value.variant.find(
        v => v.color === selectedColor.value && v.size === selectedSize.value
    );
    if (newVariant) {
        emit('save', {
            product_id: props.productId,
            variant_id: newVariant.variant_id, // Quan trọng: Trả về variant_id mới
            color: selectedColor.value,
            size: selectedSize.value
        });
    }
}
</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient border-0 py-4">
                    <div class="modal-title fw-bold color-main mb-0 fs-5">Cập nhật sản phẩm</div>
                    <button type="button" class="btn-close btn-close-black" @click="closeModal"></button>
                </div>

                <div class="modal-body p-4" v-if="productDetail">
                    <div class="main-img d-flex mb-4">
                        <img :src="`../../../../storage/${imageWithVariant}`" alt="main_image" width="100px" class="rounded">
                        <div class="fs-5 mt-3 ms-3">{{ productDetail.name }}</div>
                    </div>
                    <hr>
                    <div class="variants-section">
                        <div class="color">
                            <div class="small fw-medium">Màu sắc</div>
                            <div class="variant-colors d-flex align-items-center mt-2">
                                <button v-for="variant in uniqueVariants" :key="variant.variant_id"
                                    class="btn-color"
                                    :class="{ 'color_active': variant.color === selectedColor }"
                                    :style="{ backgroundColor: variant.color }"
                                    @click.prevent="handleChangeColor(variant.color)">
                                </button>
                            </div>
                        </div>
                        <div class="size mt-3">
                            <div class="small fw-medium">Kích thước</div>
                            <div class="variant-sizes d-flex align-items-center mt-2 flex-wrap gap-2">
                                <button v-for="variant in uniqueSizes" :key="variant.variant_id"
                                    class="btn btn-sm btn-color"
                                    :class="{ 'active bg-primary text-white border-primary': variant.size === selectedSize }"
                                    @click.prevent="handleChangeSize(variant.size)">
                                    {{ variant.size }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="btn w-100 mt-4">
                        <button class="btn bg-main rounded-5 w-100 text-white" @click="handleChangeVariant">
                            Cập nhật
                        </button>
                    </div>
                </div>

                <div v-else class="modal-body p-5 text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
:root {
    --color-main: #3497E0;
}

.color-main {
    color: var(--color-main);
}

.bg-gradient {
    background: linear-gradient(135deg, #3497E0 0%, #2a7bc4 100%);
}

.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #3497E0;
    box-shadow: 0 0 0 0.2rem rgba(52, 151, 224, 0.25);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #3497E0;
    border-color: #3497E0;
}

.btn-primary:hover {
    background-color: #2a7bc4;
    border-color: #2a7bc4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 151, 224, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
}

.modal-header {
    padding: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
}

.form-label {
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.form-label i {
    margin-right: 0.5rem;
    color: #3497E0;
}

.btn-color {
    width: 35px;
    height: 35px;
    border-radius: 50%;
    border: 2px solid rgba(0, 0, 0, 0.134);
    margin: 0 4px;
    font-weight: 300;
    font-size: 15px;
}

.color_active {
    border: 3px solid #3497E0
}
</style>
