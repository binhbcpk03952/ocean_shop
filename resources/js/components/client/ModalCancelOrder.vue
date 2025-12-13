<script setup>
import { ref, watch } from 'vue'
import api from '@/axios'

const props = defineProps({
    openModal: Boolean,
    orderId: {
        type: Number,
        required: true
    }
})

const emit = defineEmits(['close', 'success'])

const reasonCode = ref('')
const reasonText = ref('')
const loading = ref(false)
const error = ref(null)

const reasons = [
    { value: 'CHANGE_MIND', label: 'Tôi đổi ý, không muốn mua nữa' },
    { value: 'FOUND_CHEAPER', label: 'Tìm được sản phẩm rẻ hơn' },
    { value: 'ORDER_WRONG', label: 'Đặt nhầm sản phẩm' },
    { value: 'PAYMENT_ISSUE', label: 'Gặp vấn đề khi thanh toán' },
    { value: 'OTHER', label: 'Lý do khác' },
]

watch(() => props.openModal, (val) => {
    if (val) {
        reasonCode.value = ''
        reasonText.value = ''
        error.value = null
    }
})

const submitCancel = async () => {
    if (!reasonCode.value) return

    loading.value = true
    error.value = null

    try {
        await api.post(`/orders/${props.orderId}/cancel`, {
            reason_code: reasonCode.value,
            reason_text: reasonText.value
        })

        emit('success')
        emit('close')

    } catch (err) {
        error.value = err.response?.data?.message || 'Hủy đơn không thành công'
    } finally {
        loading.value = false
    }
}
</script>

<template>
    <div v-if="openModal" class="modal fade show d-block custom-modal">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">

                <!-- Header -->
                <div class="modal-header">
                    <h5 class="modal-title fw-bold text-danger">
                        Xác nhận hủy đơn hàng
                    </h5>
                    <button class="btn-close" @click="$emit('close')"></button>
                </div>

                <!-- Body -->
                <div class="modal-body">
                    <p class="mb-3">
                        Bạn có chắc chắn muốn hủy đơn hàng này không?
                        <br>
                        <small class="text-muted">
                            Thao tác này không thể hoàn tác.
                        </small>
                    </p>

                    <div class="mb-3">
                        <label class="form-label fw-medium">
                            Lý do hủy <span class="text-danger">*</span>
                        </label>
                        <select v-model="reasonCode" class="form-select">
                            <option value="">-- Chọn lý do --</option>
                            <option v-for="r in reasons" :key="r.value" :value="r.value">
                                {{ r.label }}
                            </option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">
                            Ghi chú (không bắt buộc)
                        </label>
                        <textarea
                            v-model="reasonText"
                            rows="3"
                            class="form-control"
                            placeholder="Bạn có thể ghi rõ hơn lý do hủy...">
                        </textarea>
                    </div>

                    <div v-if="error" class="alert alert-danger py-2">
                        {{ error }}
                    </div>
                </div>

                <!-- Footer -->
                <div class="modal-footer">
                    <button class="btn btn-light" @click="$emit('close')" :disabled="loading">
                        Quay lại
                    </button>

                    <button
                        class="btn btn-danger"
                        :disabled="!reasonCode || loading"
                        @click="submitCancel">
                        <span v-if="loading" class="spinner-border spinner-border-sm me-2"></span>
                        Xác nhận hủy
                    </button>
                </div>

            </div>
        </div>
    </div>
</template>

<style scoped>
.custom-modal {
    background-color: rgba(0, 0, 0, 0.5);
    position: fixed;
    inset: 0;
    z-index: 2000;
}
</style>
