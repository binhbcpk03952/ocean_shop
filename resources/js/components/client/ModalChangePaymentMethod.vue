<script setup>
import { ref } from 'vue'
const props = defineProps({
    openModal: Boolean,
    methodId: String,
})
const emit = defineEmits(['close', 'save'])
const methods = ref(null);
const handleGetMethod = (method) => {
    methods.value = method
}
const saveMethod = () => {
    emit('save', methods.value)
}
const paymentMethod = ref([
    {
        id: 'cod',
        method_name: 'Thanh toán khi nhận hàng',
        icon: 'bi bi-cash-coin',
    },
    {
        id: 'vnpay',
        method_name: 'Thanh toán VN Pay',
        icon: 'bi bi-qr-code-scan',
    }
])
const closeModal = () => {
    emit('close')
}
// console.log(props.methodId);

</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog  modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header bg-gradient border-0 px-4">
                    <div>
                        <div class="modal-title fw-medium color-main mb-0 fs-5">
                            Chọn phương thức thanh toán
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-black border-circle" @click="closeModal"></button>
                </div>
                <hr class="m-0">

                <!-- Body -->
                <div class="modal-body p-4">
                    <div class="list_method" v-if="paymentMethod" v-for="method in paymentMethod"
                        :key="paymentMethod.id">
                        <label :for="'method-' + method.id" class=" border rounded-5 my-2 py-2 px-3 w-100 d-flex align-items-center" style="cursor: pointer;">
                            <input type="radio" name="method" :id="'method-' + method.id" :value="method.id" v-model="props.methodId" @change="handleGetMethod(method)">
                            <div class="d-flex align-items-center">
                                <i :class="method.icon" class="fs-5 ms-3 me-1" ></i>
                                <span> {{ method.method_name }}</span>
                            </div>
                        </label>
                    </div>
                </div>
                <hr class="m-0">
                <div class="modal-footer">
                    <button class="w-100 btn bg-main text-white"  @click="saveMethod">Xác nhận</button>
                </div>
            </div>
        </div>
    </div>
</template>
<style>
    .modal-body {
        height: 300px;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
