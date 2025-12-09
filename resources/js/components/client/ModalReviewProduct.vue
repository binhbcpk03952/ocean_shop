<script setup>
import { ref, reactive, watch } from 'vue';
import api from '../../axios'; // Đường dẫn tới file axios của bạn
import { Toast } from "bootstrap";

// Props nhận từ cha
const props = defineProps({
    modelValue: Boolean, // Trạng thái đóng mở modal (v-model)
    product: Object      // Thông tin sản phẩm cần đánh giá
});

const emit = defineEmits(['update:modelValue', 'review-submitted']);

const isLoading = ref(false);

const form = reactive({
    rating: 0,
    content: ''
});

// Reset form khi mở modal mới
watch(() => props.product, (newVal) => {
    if (newVal) {
        form.rating = 0;
        form.content = '';
        // Nếu muốn hiển thị đánh giá cũ (nếu có), bạn cần gọi API get review của user tại đây
    }
});

const closeModal = () => {
    emit('update:modelValue', false);
};

const submitReview = async () => {
    if (form.rating === 0) {
        alert("Vui lòng chọn số sao!");
        return;
    }

    isLoading.value = true;
    try {
        const res = await api.post('reviews', {
            product_id: props.product.product_id, // Hoặc id tùy database
            rating: form.rating,
            content: form.content
        });

        if (res.status === 200) {
            alert("Đánh giá thành công!"); // Hoặc dùng Toast
            emit('review-submitted'); // Báo cho cha biết để reload lại list nếu cần
            closeModal();
        }
    } catch (error) {
        console.error(error);
        const msg = error.response?.data?.message || "Có lỗi xảy ra!";
        alert(msg);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div v-if="modelValue" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Đánh giá sản phẩm</h5>
                    <button type="button" class="btn-close" @click="closeModal"></button>
                </div>

                <div class="modal-body">
                    <div v-if="product" class="d-flex align-items-center mb-3 border-bottom pb-3">
                        <img :src="`../../../../storage/${product.image_url || product.image}`"
                             class="rounded me-3"
                             style="width: 50px; height: 50px; object-fit: cover;">
                        <div>
                            <div class="fw-bold text-truncate" style="max-width: 250px;">{{ product.name }}</div>
                            <small class="text-muted">Phân loại: {{ product.color }} - {{ product.size }}</small>
                        </div>
                    </div>

                    <div class="text-center mb-3">
                        <div class="mb-2">Chất lượng sản phẩm thế nào?</div>
                        <div class="d-flex justify-content-center gap-2">
                            <span v-for="n in 5" :key="n"
                                  class="star-icon"
                                  :class="{ active: n <= form.rating }"
                                  @click="form.rating = n">
                                ★
                            </span>
                        </div>
                        <div class="mt-2 fw-bold text-warning">
                            {{ form.rating ? (form.rating === 5 ? 'Tuyệt vời' : (form.rating === 4 ? 'Hài lòng' : 'Bình thường')) : 'Chọn mức đánh giá' }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <textarea v-model="form.content"
                                  class="form-control"
                                  rows="4"
                                  placeholder="Hãy chia sẻ nhận xét của bạn về sản phẩm này..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="closeModal">Trở lại</button>
                    <button type="button" class="btn btn-primary"
                            :disabled="isLoading"
                            @click="submitReview">
                        {{ isLoading ? 'Đang gửi...' : 'Hoàn thành' }}
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.star-icon {
    font-size: 32px;
    color: #e4e5e9;
    cursor: pointer;
    transition: color 0.2s;
}
.star-icon.active {
    color: #ffc107; /* Màu vàng */
}
.star-icon:hover {
    color: #ffdb58;
}
</style>
