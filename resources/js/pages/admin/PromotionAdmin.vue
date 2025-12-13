<script setup>
import { ref, onMounted, reactive, computed } from 'vue';
import api from '../../axios'; // Đường dẫn axios của bạn
import dayjs from 'dayjs';     // Cần cài thư viện này để xử lý ngày tháng: npm install dayjs

const promotions = ref([]);
const loading = ref(false);
const isEditMode = ref(false);
const showModal = ref(false); // Biến này dùng để toggle class modal bootstrap (hoặc dùng data-bs-toggle)

// Form data
const form = reactive({
    promotion_id: null,
    code: '',
    description: '',
    discount_type: 'percentage', // percentage | fixed
    discount_value: 0,
    min_order_amount: 0,
    max_discount_amount: 0,
    start_date: '',
    end_date: '',
    is_active: 1
});

// --- API FUNCTIONS ---

const fetchPromotions = async () => {
    loading.value = true;
    try {
        const res = await api.get('/voucher');
        promotions.value = res.data;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};

const handleSubmit = async () => {
    try {
        if (isEditMode.value) {
            await api.patch(`/voucher/${form.promotion_id}`, form);
            alert('Cập nhật thành công!');
        } else {
            await api.post('/voucher', form);
            alert('Thêm mới thành công!');
        }
        // Đóng modal thủ công (nếu dùng bootstrap js thuần thì gọi hide())
        document.getElementById('closeModalBtn').click();
        fetchPromotions();
    } catch (err) {
        alert(err.response?.data?.message || 'Có lỗi xảy ra');
    }
};

const deletePromotion = async (id) => {
    if (!confirm('Bạn có chắc muốn xóa mã này?')) return;
    try {
        await api.delete(`/promotions/${id}`);
        fetchPromotions();
    } catch (err) {
        alert('Lỗi khi xóa');
    }
};
const openModal = (mode, data = null) => {
    isEditMode.value = mode === 'edit';

    if (mode === 'edit' && data) {
        Object.assign(form, {
            ...data,
            start_date: toDatetimeLocal(data.start_date),
            end_date: toDatetimeLocal(data.end_date),
        });
    } else {
        Object.assign(form, {
            promotion_id: null,
            code: '',
            description: '',
            discount_type: 'percentage',
            discount_value: 0,
            min_order_amount: 0,
            max_discount_amount: 0,
            start_date: dayjs().format('YYYY-MM-DDTHH:mm'),
            end_date: dayjs().add(7, 'day').format('YYYY-MM-DDTHH:mm'),
            is_active: 1
        });
    }
};


// Format tiền tệ
const formatCurrency = (val) => new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);

// Format ngày hiển thị bảng (từ timestamp)
const formatTimeDate = (dateString) => {
    const date = new Date(dateString);

    const hh = String(date.getHours()).padStart(2, "0");
    const mm = String(date.getMinutes()).padStart(2, "0");

    const dd = String(date.getDate()).padStart(2, "0");
    const month = String(date.getMonth() + 1).padStart(2, "0");
    const yyyy = date.getFullYear();

    return `${hh}:${mm} ${dd}/${month}/${yyyy}`;
};
const toDatetimeLocal = (val) => {
    if (!val) return '';
    return val.replace(' ', 'T').slice(0, 16);
};



onMounted(() => {
    fetchPromotions();
});
</script>

<template>
    <div class="container-fluid p-4 bg-light min-vh-100 font-sans">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold brand-text">Quản lý Khuyến mãi</h4>
                <p class="text-muted small m-0">Tạo và chỉnh sửa mã giảm giá cho cửa hàng</p>
            </div>
            <button class="btn btn-brand text-white shadow-sm rounded-3" data-bs-toggle="modal"
                data-bs-target="#promotionModal" @click="openModal('add')">
                <i class="bi bi-plus-lg me-1"></i> Thêm mã mới
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small text-uppercase">Mã Code</th>
                            <th class="py-3 text-secondary small text-uppercase">Loại giảm</th>
                            <th class="py-3 text-secondary small text-uppercase">Giá trị</th>
                            <th class="py-3 text-secondary small text-uppercase">Thời gian</th>
                            <th class="py-3 text-secondary small text-uppercase">Đã dùng</th>
                            <th class="py-3 text-secondary small text-uppercase text-center">Trạng thái</th>
                            <th class="pe-4 py-3 text-secondary small text-uppercase text-end">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="loading">
                            <td colspan="7" class="text-center py-4">Đang tải...</td>
                        </tr>
                        <tr v-else v-for="promo in promotions" :key="promo.promotion_id">
                            <td class="ps-4">
                                <div class="fw-bold brand-text">{{ promo.code }}</div>
                                <small class="text-muted">{{ promo.description }}</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    {{ promo.discount_type === 'percentage' ? 'Phần trăm (%)' : 'Cố định (VND)' }}
                                </span>
                            </td>
                            <td class="fw-bold text-success">
                                {{ promo.discount_type === 'percentage' ? promo.discount_value + '%' :
                                formatCurrency(promo.discount_value) }}
                            </td>
                            <td class="small">
                                <div>Bắt Đầu: {{ formatTimeDate(promo.start_date) }}</div>
                                <div>Kết Thúc: {{ formatTimeDate(promo.end_date) }}</div>
                            </td>
                            <td>{{ promo.used_count }} lần</td>
                            <td class="text-center">
                                <span class="badge rounded-pill"
                                    :class="promo.is_active ? 'bg-success' : 'bg-secondary'">
                                    {{ promo.is_active ? 'Hoạt động' : 'Tạm khóa' }}
                                </span>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-outline-primary me-2" data-bs-toggle="modal"
                                    data-bs-target="#promotionModal" @click="openModal('edit', promo)">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger"
                                    @click="deletePromotion(promo.promotion_id)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="promotionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold brand-text">
                            {{ isEditMode ? 'Chỉnh sửa khuyến mãi' : 'Thêm khuyến mãi mới' }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" id="closeModalBtn"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <form @submit.prevent="handleSubmit">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Mã Code <span
                                            class="text-danger">*</span></label>
                                    <input v-model="form.code" type="text" class="form-control text-uppercase"
                                        placeholder="VD: SALE50" required>
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label small fw-bold">Mô tả</label>
                                    <input v-model="form.description" type="text" class="form-control"
                                        placeholder="Mô tả ngắn gọn về mã...">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Loại giảm giá</label>
                                    <select v-model="form.discount_type" class="form-select">
                                        <option value="percentage">Theo Phần trăm (%)</option>
                                        <option value="fixed">Số tiền cố định (VND)</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Giá trị giảm <span
                                            class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input v-model="form.discount_value" type="number" class="form-control" required
                                            min="0">
                                        <span class="input-group-text">{{ form.discount_type === 'percentage' ? '%' :
                                            'đ' }}</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Giảm tối đa</label>
                                    <div class="input-group">
                                        <input v-model="form.max_discount_amount" type="number" class="form-control"
                                            :disabled="form.discount_type === 'fixed'">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                    <div class="form-text small" v-if="form.discount_type === 'fixed'">Không áp dụng cho
                                        loại cố định</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Đơn tối thiểu</label>
                                    <div class="input-group">
                                        <input v-model="form.min_order_amount" type="number" class="form-control"
                                            min="0">
                                        <span class="input-group-text">đ</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Trạng thái</label>
                                    <select v-model="form.is_active" class="form-select">
                                        <option :value="1">Hoạt động</option>
                                        <option :value="0">Tạm khóa</option>
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Ngày bắt đầu</label>
                                    <input v-model="form.start_date" type="datetime-local" class="form-control"
                                        required>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Ngày kết thúc</label>
                                    <input v-model="form.end_date" type="datetime-local" class="form-control" required>
                                </div>

                            </div>

                            <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Hủy bỏ</button>
                                <button type="submit" class="btn btn-brand text-white shadow-sm px-4">
                                    {{ isEditMode ? 'Cập nhật' : 'Tạo mã' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<style scoped>
/* Theme Colors */
:root {
    --brand-color: #3497e0;
}

.brand-text {
    color: #3497e0 !important;
}

.btn-brand {
    background-color: #3497e0;
    border-color: #3497e0;
}

.btn-brand:hover {
    background-color: #287dbd;
    border-color: #287dbd;
}

.font-sans {
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.15);
}
</style>
