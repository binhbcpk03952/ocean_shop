<script setup>
import { ref, onMounted, watch, reactive, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../axios';
const route = useRoute();
const stockVariant = ref(null);


// API trả về sản phẩm và các mảng biến thể/hình ảnh lồng nhau
const productDetail = ref(null);
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
        selectedColor.value = newVariants[0].color;
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
    return productDetail.value.variant.filter(variant => variant.color === selectedColor.value);
})

// Theo dõi sự thay đổi của màu sắc để cập nhật size được chọn
watch(selectedColor, () => {
    if (uniqueSizes.value.length > 0) {
        selectedSize.value = uniqueSizes.value[0].size;
    }
}, { immediate: true });




// Hình ảnh chính để hiển thị
const uniquedImages = computed(() => {
    if (!productDetail.value || !productDetail.value.image) return [];
    const seen = new Set();
    return productDetail.value.image.filter(img => {
        const duplicate = seen.has(img.image_url);
        seen.add(img.image_url);
        return !duplicate;
    });
});

const currentDisplayImage = ref(null);
const mainImage = computed(() => {
    if (!productDetail.value || !productDetail.value.image) return null;
    return productDetail.value.image.find(img => img.is_main === 1) || productDetail.value.image[0];
});
watch(() => route.params.id, (newId) => {
    if (newId) {
        handleFetchProductDetailById(newId);
    }
}, { immediate: true });

watch(mainImage, (newMainImage) => {
    if (newMainImage) {
        currentDisplayImage.value = newMainImage.image_url;
    }
}, { immediate: true });

const cartValue = reactive({
    count: 1,
    variant_id: null,
})

// Lấy variant_id dựa trên màu và size đã chọn
const selectedVariant = computed(() => {
    if (!productDetail.value || !selectedColor.value || !selectedSize.value) {
        return null;
    }
    // Tìm biến thể khớp với cả màu và size đã chọn
    return productDetail.value.variant.find(v =>
        v.color === selectedColor.value && v.size === selectedSize.value
    );
});

// Cập nhật variant_id trong cartValue mỗi khi nó thay đổi
watch(selectedVariant, (newVariant) => {
    cartValue.variant_id = newVariant ? newVariant.variant_id : null;
    stockVariant.value = newVariant ? newVariant.stock : 1;
    if (cartValue.count > stockVariant.value) {
        cartValue.count = stockVariant.value;
    }
}, { immediate: true });
const handleThumbnailClick = (imageObject) => {
    currentDisplayImage.value = imageObject.image_url;
};

const handleIncreaseCount = () => {
    cartValue.count++;
    if (cartValue.count > stockVariant.value) {
        cartValue.count = stockVariant.value;
    }
}
const handleDecreaseCount = () => {
    if (cartValue.count > 1) {
        cartValue.count--;
    }
}

// addtocart
const handleAddToCart = async () => {
    if (!cartValue.variant_id) {
        alert('Vui lòng chọn biến thể sản phẩm.');
        return;
    }
    if (cartValue.count > stockVariant.value) {
        alert('Số lượng sản phẩm không đủ trong kho.');
        return;
    }
    console.log('dữ liệu gửi lên: ', cartValue);

    try {
        const res = await api.post('carts', cartValue);
        if (res.status === 200) {
            alert('Thêm sản phẩm vào giỏ hàng thành công.');
            cartValue.count = 1;
        } else {
            alert('Thêm sản phẩm vào giỏ hàng thất bại.');
        }
    } catch (err) {
        console.error('Lỗi khi thêm sản phẩm vào giỏ hàng:', err);

    }

}


</script>
<template>
    <div class="container" v-if="productDetail">
        <div class="row">
            <div class="col-lg-6 p-5">
                <div class="main-img border rounded mb-2 p-2 text-center">
                    <!-- Đảm bảo currentDisplayImage không null trước khi render -->
                    <img :src="`../../../../storage/${currentDisplayImage}`" v-if="currentDisplayImage"
                        class="img-fluid rounded-1" style="max-height: 350px;" :alt="productDetail.name">
                </div>
                <div class="thumbnail-list d-flex justify-content-start gap-2 overflow-auto">
                    <img v-for="img in uniquedImages" :key="img.image_id" :src="`../../../../storage/${img.image_url}`"
                        class="border img-thumbnail rounded-1"
                        style="width: 70px; height: 70px; object-fit: cover; cursor: pointer;" alt="Thumbnail Image"
                        @click="handleThumbnailClick(img)" :class="['img-fluid border rounded-3', {
                            'border-primary border': img.image_url === currentDisplayImage // Hiệu ứng ảnh đang chọn
                        }]">
                </div>
            </div>
            <div class="col-lg-6 p-5">
                <div class="info-product">
                    <div class="fs-4">{{ productDetail.price }}</div>
                    <div class="fs-3">{{ productDetail.name }}</div>
                    <div class="color">
                        <span>Màu sắc: </span>
                        <div class="variant-colors d-flex  align-items-center mt-2">
                            <template v-for="variant in uniqueVariants" :key="variant.variant_id">
                                <button class="btn-color" :class="{ 'color_active': variant.color === selectedColor }"
                                    :style="{ backgroundColor: variant.color, }"
                                    @click.stop.prevent="handleChangeColor(variant.color)"></button>
                            </template>
                        </div>

                    </div>
                    <div class="size">
                        <span>Kích thước: </span>
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
                    <div class="change-stock_add-cart row d-flex align-items-center">
                        <div class="col-md-4">
                            <div
                                class="chang-stock-cart d-flex justify-content-between align-items-center border rounded-4 py-2 px-3">
                                <button class="btn-custom" @click="handleDecreaseCount">
                                    <i class="bi bi-dash"></i>
                                </button>
                                <span>{{ cartValue.count }}</span>
                                <button class="btn-custom" @click="handleIncreaseCount">
                                    <i class="bi bi-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <button class="btn btn-primary rounded-4 w-100 py-2" @click="handleAddToCart">Thêm vào giỏ hàng</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Giao diện khi đang tải dữ liệu -->
    <div class="container text-center p-5" v-else>
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="mt-2">Đang tải chi tiết sản phẩm...</p>
    </div>
</template>
<style scoped>
.btn-color {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 3px solid rgba(0, 0, 0, 0.134);
    margin: 0 4px;
    font-weight: 300;
    font-size: 15px;
}

.color_active {
    border: 3px solid #3497E0
}

.btn-custom {
    border: none;
    background-color: transparent;
    font-weight: 400;
    font-size: 20px;
}
</style>
