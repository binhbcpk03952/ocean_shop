<script setup>
import { ref, onMounted, computed, watch } from 'vue';
import api from '../../axios';

// --- STATE ---
const orders = ref([]);
const loading = ref(false);
const searchQuery = ref('');
const filterStatus = ref('all');
const selectedOrder = ref(null); // Đơn hàng đang xem chi tiết

// --- API ---
const handleFetchOrders = async () => {
    loading.value = true;
    try {
        const res = await api.get('/orders_admin');
        if (res.status === 200) {
            orders.value = res.data;
        }
    } catch (err) {
        console.error('Lỗi API:', err);
    } finally {
        loading.value = false;
    }
}

// --- LOGIC LỌC DỮ LIỆU ---
const filteredOrders = computed(() => {
    return orders.value.filter(order => {
        const term = searchQuery.value.toLowerCase();
        // Tìm theo ID đơn hoặc ID user
        const matchesSearch =
            order.order_id.toString().includes(term) ||
            (order.user_id && order.user_id.toString().includes(term));

        const matchesStatus = filterStatus.value === 'all' || order.status === filterStatus.value;
        return matchesSearch && matchesStatus;
    });
});

// --- HELPER FORMAT ---
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const formatDate = (dateString) => {
    if (!dateString) return '';
    return new Date(dateString).toLocaleDateString('vi-VN', {
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit'
    });
};

// --- HELPER TRẠNG THÁI ---
const getStatusConfig = (status) => {
    switch (status) {
        case 'pending': return { label: 'Chờ xử lý', class: 'bg-warning text-dark' };
        case 'paid': return { label: 'Đã thanh toán', class: 'bg-success text-white' };
        case 'shipping': return { label: 'Đang giao hàng', class: 'bg-info text-white' };
        case 'completed': return { label: 'Hoàn thành', class: 'bg-success text-white' };
        case 'failed': return { label: 'Thất bại', class: 'bg-danger text-white' };
        case 'cancelled': return { label: 'Đã hủy', class: 'bg-secondary text-white' };
        default: return { label: status, class: 'bg-light text-dark border' };
    }
};

// --- CẤU HÌNH CHUYỂN ĐỔI TRẠNG THÁI ---
const STATUS_TRANSITIONS = {
    // Đang chờ xử lý -> Chỉ được: Giao hàng (Tiến) hoặc Hủy (Lùi/Dừng)
    'pending': [
        { action: 'shipping', label: 'Xác nhận & Giao', class: 'btn-primary', icon: 'bi-box-seam' },
        { action: 'cancelled', label: 'Hủy đơn', class: 'btn-outline-danger', icon: 'bi-x-circle' }
    ],
    // Đã thanh toán (VNPAY) -> Cũng cần giao hàng
    'paid': [
        { action: 'shipping', label: 'Giao hàng', class: 'btn-primary', icon: 'bi-box-seam' },
        { action: 'cancelled', label: 'Hoàn tiền & Hủy', class: 'btn-outline-danger', icon: 'bi-cash' } // Logic hoàn tiền phức tạp hơn, nhưng trạng thái là hủy
    ],
    // Đang giao -> Chỉ được: Hoàn thành (Tiến) hoặc Hủy/Hoàn hàng (Lùi)
    'shipping': [
        { action: 'completed', label: 'Đã giao xong', class: 'btn-success', icon: 'bi-check-lg' },
        { action: 'cancelled', label: 'Khách bom hàng', class: 'btn-outline-danger', icon: 'bi-arrow-return-left' }
    ],
    // Hoàn thành hoặc Đã hủy -> Không làm gì được nữa (Kết thúc)
    'completed': [],
    'cancelled': [],
    'failed': []
};

// Hàm lấy danh sách hành động dựa trên trạng thái hiện tại
const getNextActions = (currentStatus) => {
    return STATUS_TRANSITIONS[currentStatus] || [];
};

// Hàm xử lý cập nhật (Gọi API)
const handleUpdateStatus = async (orderId, newStatus) => {
    if (!confirm(`Bạn có chắc muốn chuyển đơn #${orderId} sang trạng thái "${newStatus}"?`)) return;

    try {
        loading.value = true; // Bật loading nếu muốn

        // Gọi API PUT/PATCH để cập nhật
        const res = await api.post(`/update-order-status/${orderId}`, { status: newStatus });
        if (res.status === 200) {
            // GIẢ LẬP CẬP NHẬT UI NGAY LẬP TỨC (Optimistic Update)
            const orderIndex = orders.value.findIndex(o => o.order_id === orderId);
            if (orderIndex !== -1) {
                orders.value[orderIndex].status = newStatus;

                // Nếu đang mở Modal chi tiết, cập nhật luôn trong modal
                if (selectedOrder.value && selectedOrder.value.order_id === orderId) {
                    selectedOrder.value.status = newStatus;
                }
            }

            alert("Cập nhật trạng thái thành công!");
        }

    } catch (err) {
        console.error("Lỗi cập nhật:", err);
        alert("Có lỗi xảy ra, vui lòng thử lại.");
    } finally {
        // loading.value = false;
    }
};
const getStatusLabel = (val) => {
    const option = statusOptions.find(opt => opt.value === val);
    return option ? option.label : val;
};

// --- HELPER LẤY ẢNH SẢN PHẨM ---
const getProductImage = (item) => {
    // Tìm ảnh chính (is_main = 1) hoặc lấy ảnh đầu tiên
    const images = item.variant?.image || [];
    const mainImg = images.find(img => img.is_main === 1) || images[0];

    // Nếu có ảnh thì trả về đường dẫn (bạn cần nối thêm base URL nếu API trả về relative path)
    // Ví dụ: return `http://your-api.com/${mainImg.image_url}`;
    return mainImg ? mainImg.image_url : 'https://via.placeholder.com/60';
};

// --- XỬ LÝ MODAL ---
const openOrderDetails = (order) => {
    selectedOrder.value = order;
};

onMounted(() => {
    handleFetchOrders();
});
</script>

<template>
    <div class="container-fluid p-4 bg-light min-vh-100 font-sans">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold brand-text">Quản lý đơn hàng</h4>
                <p class="text-muted small m-0">Tổng quan danh sách đơn đặt hàng</p>
            </div>
            <button class="btn btn-brand text-white shadow-sm rounded-3">
                <i class="bi bi-download me-1"></i> Xuất báo cáo
            </button>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-3">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                            <input v-model="searchQuery" type="text" class="form-control border-start-0 ps-0"
                                placeholder="Tìm ID đơn hàng...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select v-model="filterStatus" class="form-select">
                            <option value="all">Tất cả trạng thái</option>
                            <option value="pending">⏳ Chờ xử lý</option>
                            <option value="paid">💰 Đã thanh toán</option>
                            <option value="failed">❌ Thất bại</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4 py-3 text-secondary small text-uppercase">Mã đơn</th>
                            <th class="py-3 text-secondary small text-uppercase">Khách hàng</th>
                            <th class="py-3 text-secondary small text-uppercase">Ngày tạo</th>
                            <th class="py-3 text-secondary small text-uppercase">Phương thức</th>
                            <th class="py-3 text-secondary small text-uppercase text-end">Tổng tiền</th>
                            <th class="py-3 text-secondary small text-uppercase text-center">Trạng thái</th>
                            <th></th>
                            <th class="pe-4 py-3 text-secondary small text-uppercase text-end">Chi tiết</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="order in filteredOrders" :key="order.order_id">
                            <td class="ps-4 fw-bold text-primary">#OCEAN-{{ order.order_id }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div
                                        class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                                        {{ order.user_id }}
                                    </div>
                                    <span class="fw-medium small">{{ order.user.name }}</span>
                                </div>
                            </td>
                            <td class="text-muted small">{{ formatDate(order.created_at) }}</td>
                            <td>
                                <span class="badge bg-light text-dark border text-uppercase">{{ order.payment_method
                                }}</span>
                            </td>
                            <td class="text-end fw-bold">{{ formatCurrency(order.final_amount) }}</td>
                            <td class="text-center">
                                <span class="badge rounded-pill px-3 py-2 fw-normal"
                                    :class="getStatusConfig(order.status).class">
                                    {{ getStatusConfig(order.status).label }}
                                </span>
                            </td>
                            <td>
                                <template v-for="action in getNextActions(order.status)" :key="action.action">
                                    <button class="btn btn-sm me-1 rounded-3" :class="action.class"
                                        :title="action.label"
                                        @click="handleUpdateStatus(order.order_id, action.action)">
                                        <i :class="['bi', action.icon]"></i>
                                        {{ action.label }}
                                    </button>
                                </template>
                            </td>
                            <td class="pe-4 text-end" style="min-width: 180px;">
                                <button class="btn btn-sm btn-outline-secondary me-2 rounded-3" title="Xem chi tiết"
                                    data-bs-toggle="modal" data-bs-target="#orderDetailModal"
                                    @click="openOrderDetails(order)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal fade" id="orderDetailModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow-lg" v-if="selectedOrder">
                    <div class="modal-header border-bottom-0">
                        <h5 class="modal-title fw-bold brand-text">
                            Chi tiết đơn hàng #{{ selectedOrder.order_id }}
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body pt-0">
                        <div class="row mb-4 p-3 bg-light rounded-3 g-3 mx-0">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Ngày đặt</small>
                                <span class="fw-bold">{{ formatDate(selectedOrder.created_at) }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Thanh toán</small>
                                <span class="text-uppercase fw-bold">{{ selectedOrder.payment_method }}</span>
                            </div>
                            <div class="col-md-12">
                                <small class="text-muted d-block">Thông tin giao hàng</small>
                                <span class="fw-medium">
                                    <span class="fw-bold">{{ selectedOrder.addresses.recipient_name }}</span> | {{
                                        selectedOrder.addresses.recipient_phone }}
                                    <br>
                                    {{ selectedOrder.addresses.street_address }}, {{ selectedOrder.addresses.ward }}, {{
                                        selectedOrder.addresses.district }}, {{ selectedOrder.addresses.province }}
                                </span>
                            </div>
                        </div>

                        <h6 class="fw-bold mb-3 ps-1">Sản phẩm ({{ selectedOrder.order_item.length }})</h6>
                        <div class="product-list">
                            <div v-for="item in selectedOrder.order_item" :key="item.order_item_id"
                                class="d-flex align-items-center mb-3 pb-3 border-bottom last-no-border">
                                <img :src="'../../../../storage/' + getProductImage(item)"
                                    class="rounded-3 border object-fit-cover" width="70" height="70" alt="Product">

                                <div class="ms-3 flex-grow-1">
                                    <h6 class="mb-1 fw-bold text-dark text-truncate-2">
                                        {{ item.variant?.product?.name || 'Sản phẩm không tồn tại' }}
                                    </h6>
                                    <div class="d-flex gap-2 text-muted small">
                                        <span class="badge bg-light text-dark border">
                                            Size: {{ item.variant?.size }}
                                        </span>
                                        <span class="badge bg-light text-dark border d-flex align-items-center gap-1">
                                            Màu: <span class="color-dot"
                                                :style="{ backgroundColor: item.variant?.color }"></span>
                                        </span>
                                    </div>
                                </div>

                                <div class="text-end ms-3">
                                    <div class="text-muted small">x{{ item.quantity }}</div>
                                    <div class="fw-bold brand-text">{{ formatCurrency(item.price) }}</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <div class="text-end" style="min-width: 200px;">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Phí vận chuyển:</span>
                                    <span>{{ formatCurrency(selectedOrder.shipping_fee) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Giảm giá:</span>
                                    <span>-{{ formatCurrency(selectedOrder.discount_amount) }}</span>
                                </div>
                                <div class="d-flex justify-content-between border-top pt-2 mt-2">
                                    <span class="fw-bold fs-5">Tổng cộng:</span>
                                    <span class="fw-bold fs-5 brand-text">{{ formatCurrency(selectedOrder.final_amount)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 justify-content-between">
                        <div>
                            <span class="text-muted small me-2">Trạng thái:</span>
                            <span class="badge rounded-pill px-3 py-2 fw-normal"
                                :class="getStatusConfig(selectedOrder.status).class">
                                {{ getStatusConfig(selectedOrder.status).label }}
                            </span>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Đóng</button>

                            <button v-for="action in getNextActions(selectedOrder.status)" :key="action.action"
                                type="button" class="btn rounded-3 text-white shadow-sm"
                                :class="action.class.replace('btn-outline-', 'btn-')"
                                @click="handleUpdateStatus(selectedOrder.order_id, action.action)">
                                <i :class="['bi me-1', action.icon]"></i> {{ action.label }}
                            </button>
                        </div>
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

.text-primary {
    color: #3497e0 !important;
}

.bg-primary-subtle {
    background-color: rgba(52, 151, 224, 0.1) !important;
}

/* Table Styles */
.avatar-sm {
    width: 32px;
    height: 32px;
    font-size: 12px;
}

/* Product List in Modal */
.color-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 1px solid #ddd;
    display: inline-block;
}

.last-no-border:last-child {
    border-bottom: none !important;
    margin-bottom: 0 !important;
    padding-bottom: 0 !important;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Form Styles */
.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.15);
}

.border-brand {
    border-color: #b3d7f2;
    /* Màu xanh nhạt */
}

.border-brand:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.2rem rgba(52, 151, 224, 0.25);
}

/* Hiệu ứng disable cho nút Lưu */
.btn-brand:disabled {
    background-color: #8bcae4;
    border-color: #8bcae4;
    cursor: not-allowed;
}
</style>
