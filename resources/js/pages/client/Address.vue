<script setup>
import { ref, onMounted } from 'vue';
import CreateAddress from './CreateAddress.vue';
import api from '../../axios';

// State quản lý Modal
const openModal = ref(false);
const mode = ref('create');
const selectedAddress = ref(null); // Biến lưu địa chỉ đang sửa

// Mở modal thêm mới
const handleAddAddress = () => {
    selectedAddress.value = null; // Reset dữ liệu cũ
    mode.value = 'create';
    openModal.value = true;
};

// Mở modal cập nhật (Sửa lỗi gọi hàm openModal trực tiếp trong template)
const handleEditAddress = (addr) => {
    selectedAddress.value = addr;
    mode.value = 'edit';
    openModal.value = true;
};

const handleCloseModal = () => {
    openModal.value = false;
    selectedAddress.value = null;
};

// Khi save xong thì fetch lại data
const handleSaveModal = () => {
    openModal.value = false;
    handleFetchAddress();
};

// Dữ liệu địa chỉ
const addresses = ref([]);

const handleFetchAddress = async () => {
    try {
        const res = await api.get('/addresses');
        if (res.status === 200) {
            addresses.value = res.data;
            console.log('Dữ liệu lấy về:', addresses.value);
        }
    } catch (err) {
        console.log('Lỗi khi gọi API: ', err);
    }
};

const deleteAddress = async (id) => {
    if (confirm('Bạn có chắc muốn xóa địa chỉ này?')) {
        // Cần gọi API xóa thật (nếu có API)
        // await api.delete(`/addresses/${id}`);

        // Cập nhật giao diện (Sửa logic lọc theo address_id)
        addresses.value = addresses.value.filter(a => a.address_id !== id);
    }
};

const setDefault = async (id) => {
    // Cần gọi API set default thật (nếu có API)

    // Cập nhật giao diện (Sửa logic dùng is_default và address_id)
    addresses.value.forEach(a => {
        a.is_default = a.address_id === id;
    });
};

onMounted(() => {
    handleFetchAddress();
});
</script>

<template>
    <CreateAddress :open-modal="openModal" :mode="mode" :address-data="selectedAddress" @close="handleCloseModal"
        @save="handleSaveModal" />

    <div class="card border-0 shadow-sm rounded-4 h-100 container">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">Sổ địa chỉ</h5>
                <p class="text-muted small mb-0">Quản lý địa chỉ nhận hàng</p>
            </div>
            <button class="btn btn-brand rounded-3 px-3 py-2 shadow-sm d-flex align-items-center gap-2"
                @click="handleAddAddress">
                <i class="bi bi-plus-lg"></i> <span class="d-none d-sm-inline">Thêm địa chỉ mới</span>
            </button>
        </div>

        <hr class="mx-4 mt-2 opacity-25">

        <div class="card-body px-4 py-2">
            <div v-if="addresses.length > 0">
                <div v-for="(addr, index) in addresses" :key="addr.address_id" class="address-item py-4 border-bottom">
                    <div class="row">

                        <div class="col-md-8 mb-3 mb-md-0">
                            <div class="d-flex align-items-center flex-wrap gap-2 mb-2">
                                <span class="fw-bold text-dark fs-6">{{ addr.recipient_name }}</span>
                                <span class="text-muted border-start ps-2 small">{{ addr.recipient_phone }}</span>
                            </div>

                            <div class="text-secondary mb-1 text-address">
                                {{ addr.street_address }}
                            </div>

                            <div class="text-secondary text-address mb-2">
                                {{ addr.ward }}, {{ addr.district }}, {{ addr.province }}
                            </div>

                            <div class="d-flex gap-2 mt-2">
                                <span v-if="addr.is_default === 1" class="badge badge-default">
                                    Mặc định
                                </span>

                                <span v-if="addr.type" class="badge badge-type">
                                    {{ addr.type === 'home' ? 'Nhà riêng' : 'Văn phòng' }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="col-md-4 d-flex flex-column align-items-md-end align-items-start justify-content-center gap-2">

                            <div class="d-flex gap-3">
                                <button class="btn btn-link p-0 text-decoration-none text-brand fw-medium small"
                                    @click="emit('edit', addr)">
                                    Cập nhật
                                </button>

                                <button v-if="addr.is_default !== 1"
                                    class="btn btn-link p-0 text-decoration-none text-muted fw-medium small hover-danger"
                                    @click="emit('delete', addr.address_id)">
                                    Xóa
                                </button>
                            </div>

                            <span v-if="addr.is_default === 1" class="btn btn-default btn-sm rounded-1 px-3 mt-1" style="font-size: 12px;">
                                Mặc định
                            </span>
                            <button v-else
                                class="btn btn-outline-secondary btn-sm rounded-1 px-3 mt-1" style="font-size: 12px;"
                                @click="emit('set-default', addr.address_id)">
                                Thiết lập mặc định
                            </button>

                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="text-center py-5">
                <div class="mb-3 text-muted opacity-50">
                    <i class="bi bi-geo-alt display-1"></i>
                </div>
                <p class="text-muted">Bạn chưa lưu địa chỉ nào.</p>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* Màu chủ đạo */
:root {
    --brand-color: #3497e0;
}

.text-brand {
    color: #3497e0;
}

.bg-brand {
    background-color: #3497e0;
}

/* Nút Brand */
.btn-brand {
    background-color: #3497e0;
    border-color: #3497e0;
    color: white;
    transition: all 0.3s;
}

.btn-brand:hover {
    background-color: #287dbd;
    transform: translateY(-1px);
}

/* Badge Mặc định */
.badge-default-outline {
    color: #3497e0;
    border: 1px solid #3497e0;
    background-color: rgba(52, 151, 224, 0.05);
    font-weight: 500;
    padding: 5px 10px;
}

/* Item Address */
.address-item:last-child {
    border-bottom: none !important;
}

.hover-brand:hover {
    color: #3497e0 !important;
}

.small-text {
    font-size: 0.85rem;
}

/* Form Styles trong Modal */
.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.15);
}

.form-check-input:checked {
    background-color: #3497e0;
    border-color: #3497e0;
}
.btn-default {
   background-color: #3497e0;
   color: #fff;
}
</style>
