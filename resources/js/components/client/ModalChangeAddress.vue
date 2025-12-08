<script setup>
import { ref, watch } from 'vue' // Thêm watch

const props = defineProps({
    openModal: Boolean,
    addressId: Number,
    addresses: Array,
})

const emit = defineEmits(['close', 'save'])

// Tạo biến cục bộ để lưu giá trị đang chọn
const selectedId = ref(props.addressId)

// Theo dõi sự thay đổi của props.addressId để cập nhật vào biến cục bộ
// (Giúp hiển thị đúng radio button đang được chọn khi mở modal)
watch(() => props.addressId, (newVal) => {
    selectedId.value = newVal
})

const saveAddress = () => {
    // Emit giá trị đã chọn ra bên ngoài
    emit('save', selectedId.value)
}

const closeModal = () => {
    emit('close')
}
</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient border-0 px-4">
                    <div>
                        <div class="modal-title fw-medium color-main mb-0 fs-5">
                            Địa chỉ nhận hàng
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-black border-circle" @click="closeModal"></button>
                </div>
                <hr class="m-0">

                <div class="modal-body p-4">
                    <div class="list_method" v-if="addresses" v-for="address in addresses" :key="address.address_id">
                        <label :for="'address-' + address.address_id"
                               class="border rounded-5 my-2 py-2 px-3 w-100 d-flex align-items-center"
                               style="cursor: pointer;"
                               :class="{'border-primary bg-light': selectedId === address.address_id}"> <input type="radio"
                                   name="method"
                                   :id="'address-' + address.address_id"
                                   :value="address.address_id"
                                   v-model="selectedId">

                            <div class="d-flex align-items-center ms-2">
                                <span class="fw-bold me-2">{{ address.recipient_name }}</span>
                                <span class="text-muted small"> | {{ address.recipient_phone }} - {{ address.street_address }}, {{ address.ward }}, {{ address.district }}, {{ address.province }}</span>
                            </div>
                        </label>
                    </div>
                </div>
                <hr class="m-0">
                <div class="modal-footer">
                    <button class="w-100 btn bg-main text-white" @click="saveAddress">Xác nhận</button>
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
    /* Style cho radio button */
    input[type="radio"] {
        accent-color: #3497e0;
        transform: scale(1.2);
    }
</style>
