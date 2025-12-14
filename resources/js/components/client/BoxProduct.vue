<script setup>
import { computed, ref, watch } from 'vue'
const props = defineProps({
    product: Object,
    column: { type: Number, default: 4 },
    // isFavorite: { type: Boolean, default: false } // truyền từ cha xuống
})
const selectedColor = ref(null);
const selectedImages = ref(null);

const uniqueVariants = computed(() => {
    if (!props.product || !props.product.variant) return [];
    const seen = new Set();
    return props.product.variant.filter(variant => {
        const duplicate = seen.has(variant.color);
        seen.add(variant.color);
        return !duplicate;
    });
});

// Sử dụng watch để gán màu mặc định một cách an toàn
watch(uniqueVariants, (newVariants) => {
    if (newVariants.length > 0 && !selectedColor.value) {
        selectedColor.value = newVariants[0].color;
    }
}, { immediate: true });



const imageWithVariant = computed(() => {
    // Kiểm tra an toàn dữ liệu đầu vào
    if (!props.product || !props.product.image || !props.product.variant) return null;
    if (selectedColor.value) {
        const targetVariant = props.product.variant.find(v => v.color === selectedColor.value);

        if (targetVariant) {
            const targetImage = props.product.image.find(img => img.variant_id === targetVariant.variant_id);
            if (targetImage) return targetImage.image_url;
        }
    }

    const mainImage = props.product.image.find(img => img.is_main === 1);
    if (mainImage) return mainImage.image_url;
    return props.product.image[0]?.image_url || null;
});
// console.log(imageWithVariant.value);


// ✅ Gửi sự kiện khi bấm vào icon
const emit = defineEmits(['toggle-favorite', 'add_to_cart'])

// const handleToggleFavorite = () => {
//     emit('toggle-favorite', props.product.product_id)
// }
const handleAddToCart = (product_id) => {
    emit('add_to_cart', product_id)
}

const handleChangeColor = (color) => {
    selectedColor.value = color;
}
</script>
<template>
    <router-link :class="`col-md-${props.column} my-2 nav-link p-2`" :to="`/products/` +  props.product.product_id"
       >
        <div class="box_product">
            <!-- <button type="button" class="btn btn-favorite position-absolute top-0 end-0 m-2"
                :class="props.isFavorite ? 'btn-danger' : 'btn-ouline-danger'"
                @click.stop.prevent="handleToggleFavorite">
                <i class="bi" :class="props.isFavorite ? 'bi-heart-fill' : 'bi-heart'"></i>
            </button> -->
            <div class="box_image d-flex justify-content-center align-items-center">
                <img v-if="imageWithVariant" :src="'../../../../storage/' + imageWithVariant" alt="name-product"
                    class="img-product w-100">
            </div>
            <div class="price-product fw-medium text-black fs-4 fw-medium px-2">
                {{ Number(props.product.price).toLocaleString('vi-VN') }}đ
            </div>

            <div class="name-product fw-light text-ellipsis mb-2 px-2" style="z-index: 10;">
                {{ props.product.name }}
            </div>
            <div class="variant-colors d-flex  align-items-center px-2">
                <template v-for="variant in uniqueVariants" :key="variant.variant_id">
                    <button class="btn-color" :class="{ 'color_active': selectedColor === variant.color }"
                        :style="{ backgroundColor: variant.color, }" @click.stop.prevent="handleChangeColor(variant.color)"></button>
                </template>
            </div>
        </div>
    </router-link>
</template>
<style scoped>
.box_image {
    /* height: 320px; */
    padding: 0 10px;
    z-index: 0;
    background-color: transparent;
}

.btn-favorite {
    background-color: #f8f9fa;
    border-radius: 50%;
    /* border: 1px solid #ccc; */
    color: #dc3545;
    transition: 0.3s;
    width: 40px;
    height: 40px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 9px;
    z-index: 3;
}

.btn-favorite:hover {
    background-color: #ffe5e5;
}

/* Khi đã yêu thích */
.btn-favorite.active {
    background-color: #dc3545;
    color: #fff;
    border-color: #dc3545;
}

.btn-favorite i {
    font-size: 1.2rem;
}

.text-ellipsis {
    white-space: nowrap;
    /* Không xuống hàng */
    overflow: hidden;
    /* Ẩn phần vượt khung */
    text-overflow: ellipsis;
    /* Hiển thị "..." */
}

.btn-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: 1px solid rgba(0, 0, 0, 0.134);
    margin-right: 5px
}

.color_active {
    border: 3px solid #3497E0;
}
</style>
