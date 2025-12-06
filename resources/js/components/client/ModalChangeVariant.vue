<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../../axios';
const props = defineProps({
    openModal: Boolean,
    productId: {
        type: Number,
        required: true
    },
    color: {
        type: String,
        default: null
    },
    size: {
        type: String,
        default: null
    }

})
const productDetail = ref(null)
const handleFetchProductDetailById = async (id) => {
    try {
        const res = await api.get(`products/${id}`);
        if (res.status === 200) {
            productDetail.value = res.data;
        } else {
            productDetail.value = null;
        }
    } catch (err) {
        console.error("Lỗi khi tải chi tiết sản phẩm:", err);
        productDetail.value = null;
    };
}

// Màu sắc
const selectedColor = ref(null);
const handleChangeColor = (color) => {
    selectedColor.value = color;
}

const uniqueVariants = computed(() => {
    if (!productDetail.value || !productDetail.value.variant) return [];
    const seen = new Set();
    return productDetail.value.variant.filter(variant => {
        const duplicate = seen.has(variant.color);
        seen.add(variant.color);
        return !duplicate;
    });
});
watch(uniqueVariants, (newVariants) => {
    if (newVariants.length > 0 && !selectedColor.value) {
        selectedColor.value = props.color || newVariants[0].color;
    }
}, { immediate: true });

// kích thước
const selectedSize = ref(null);
const handleChangeSize = (size) => {
    selectedSize.value = size;
}

const uniqueSizes = computed(() => {
    if (!productDetail.value || !productDetail.value.variant || !selectedColor.value) {
        return [];
    }
    const seen = new Set();
    return productDetail.value.variant
        .filter(variant => variant.color === selectedColor.value)
        .filter(variant => {
            const duplicate = seen.has(variant.size);
            seen.add(variant.size);
            return !duplicate;
        });
})

// Theo dõi sự thay đổi của màu sắc để cập nhật size được chọn
watch(selectedColor, () => {
    if (uniqueSizes.value.length > 0) {
        selectedSize.value = props.size || uniqueSizes.value[0].size;
    }
}, { immediate: true });

const imageWithVariant = computed(() => {
    // Kiểm tra an toàn dữ liệu đầu vào
    if (!productDetail.value || !productDetail.value.image || !productDetail.value.variant) return null;

    if (selectedColor.value) {
        const targetVariant = productDetail.value.variant.find(v => v.color === selectedColor.value && v.size === selectedSize.value);

        if (targetVariant) {
            const targetImage = productDetail.value.image.find(img => img.variant_id === targetVariant.variant_id);
            if (targetImage) return targetImage.image_url;
        }
    }

    const mainImage = productDetail.value.image.find(img => img.is_main === 1);
    if (mainImage) return mainImage.image_url;
    return productDetail.value.image[0]?.image_url || null;
});



watch(() => props.productId, (newId) => {
    if (newId) {
        handleFetchProductDetailById(newId);
    }
}, { immediate: true });

onMounted(() => {
    if (props.productId) {
        handleFetchProductDetailById(props.productId);
    }
});
watch(() => props.openModal, (newVal) => {
    if (newVal && props.productId) {
        handleFetchProductDetailById(props.productId);
    }
}, { immediate: true });
const emit = defineEmits(['close', 'save'])
const closeModal = () => {
    emit('close')
}
</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header bg-gradient border-0 py-4">
                    <div>
                        <div class="modal-title fw-bold color-main mb-0 fs-5">
                            Cập nhật sản phẩm
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-black" @click="closeModal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="main-img d-flex mb-4">
                        <img :src="`../../../../storage/${imageWithVariant}`" alt="main_image" width="150px">
                        <div class="fs-3">{{ productDetail.name }}</div>
                    </div>
                    <hr>
                    <div class="variants-section">
                        <div class="color">
                            <div class="small fw-medium">Màu sắc</div>
                            <div class="variant-colors d-flex  align-items-center mt-2">
                                <template v-for="variant in uniqueVariants" :key="variant.variant_id">
                                    <button class="btn-color"
                                        :class="{ 'color_active': variant.color === selectedColor }"
                                        :style="{ backgroundColor: variant.color, }"
                                        @click.stop.prevent="handleChangeColor(variant.color)"></button>
                                </template>
                            </div>
                        </div>
                        <div class="size">
                            <div class="small fw-medium mt-4">Kích thước</div>
                            <div class="variant-sizes d-flex  align-items-center my-2">
                                <template v-for="variant in uniqueSizes" :key="variant.variant_id">
                                    <button class="btn-color" :class="{ 'color_active': variant.size === selectedSize }"
                                        style="background-color: transparent;"
                                        @click.stop.prevent="handleChangeSize(variant.size)">
                                        {{ variant.size }}
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                    <div class="btn w-100 ">
                        <button class="btn bg-main rounded-5 w-100 text-white">Cập nhật</button>
                    </div>

                    <!-- Footer -->
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
