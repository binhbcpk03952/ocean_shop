<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
    openModal: Boolean,
    voucherId: [Number, String], // Có thể là số hoặc chuỗi
    vouchers: Array,
})

const emit = defineEmits(['close', 'save'])

// Tạo biến local để lưu giá trị voucher đang chọn
const selectedVoucherId = ref(props.voucherId);

// Khi mở modal lại, cập nhật lại biến local theo props từ cha truyền vào
watch(() => props.voucherId, (newVal) => {
    selectedVoucherId.value = newVal;
});

const saveVoucher = () => {
    // Emit giá trị local ra ngoài
    emit('save', selectedVoucherId.value)
}

const closeModal = () => {
    // Reset lại về giá trị cũ nếu đóng mà không lưu (optional)
    selectedVoucherId.value = props.voucherId;
    emit('close')
}
</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient border-0 px-4">
                    <div class="modal-title fw-medium color-main mb-0 fs-5">
                        Chọn mã giảm giá
                    </div>
                    <button type="button" class="btn-close btn-close-black border-circle" @click="closeModal"></button>
                </div>
                <hr class="m-0">

                <div class="modal-body p-4">
                    <template v-if="props.vouchers && props.vouchers.length > 0">
                        <div class="list_method" v-for="voucher in props.vouchers" :key="voucher.promotion_id">
                            <label :for="'voucher-' + voucher.promotion_id"
                                class="border rounded-5 my-2 py-2 px-3 w-100 d-flex align-items-center"
                                :class="{'border-primary bg-light': selectedVoucherId === voucher.promotion_id}"
                                style="cursor: pointer;">

                                <input type="radio"
                                    name="method"
                                    :id="'voucher-' + voucher.promotion_id"
                                    :value="voucher.promotion_id"
                                    v-model="selectedVoucherId"
                                    class="form-check-input me-3">

                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark"> {{ voucher.code }}</span>
                                    <small class="text-muted"> {{ voucher.description }}</small>
                                </div>
                            </label>
                        </div>
                    </template>
                    <div v-else class="text-center py-5 text-muted">
                        Không có voucher nào khả dụng
                    </div>
                </div>
                <hr class="m-0">
                <div class="modal-footer">
                    <button class="w-100 btn bg-main text-white btn-brand" @click="saveVoucher">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
    .modal-body {
        height: 300px;
        max-height: 300px;
        overflow-y: auto;
    }
    .bg-main {
        background-color: #3497e0;
    }
    /* Style khi active */
    .border-primary {
        border-color: #3497e0 !important;
    }
</style>
