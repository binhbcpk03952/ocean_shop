<script setup>
import { ref, computed, onMounted } from 'vue';
import api from '../../axios'; // Import axios config của bạn
import { useRouter, useRoute } from 'vue-router';
import ModalReviewProduct from '../../components/client/ModalReviewProduct.vue';
// ROUTER
const router = useRouter();
const route = useRoute()
// --- STATE ---
const orders = ref([]);
const loading = ref(false);

// Định nghĩa Tabs (Map với status của Database)
const tabs = [
    { id: 'all', label: 'Tất cả' },
    { id: 'pending', label: 'Chờ xác nhận' },
    { id: 'shipping', label: 'Đang giao' },
    { id: 'completed', label: 'Hoàn thành' },
    { id: 'cancelled', label: 'Đã hủy' }
];

// --- API ---
const fetchOrders = async () => {
    loading.value = true;
    try {
        const res = await api.get('/orders'); // API lấy danh sách đơn hàng
        if (res.status === 200) {
            orders.value = res.data;
        }
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
};
const activeTab = computed(() => {
    return route.query.type || 'all';
});

// --- LOGIC HELPER ---
const selectTab = (tabId) => {
    // router.push giúp đổi URL mà KHÔNG load lại trang
    // dùng { ...route.query } để giữ lại các param khác nếu có (ví dụ ?page=1)
    router.push({ query: { ...route.query, type: tabId } });
};
// 1. Lọc đơn hàng theo Tab
const filteredOrders = computed(() => {
    if (activeTab.value === 'all') return orders.value;

    // Logic gộp trạng thái (nếu cần)
    if (activeTab.value === 'shipping') {
        return orders.value.filter(o => ['shipping', 'confirmed'].includes(o.status));
    }

    return orders.value.filter(o => o.status === activeTab.value);
});

// 2. Format Tiền tệ
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

// 3. Lấy ảnh sản phẩm từ cấu trúc JSON lồng nhau
const getProductImage = (item) => {
    const images = item.variant?.image || [];
    const mainImg = images.find(img => img.is_main === 1) || images[0];

    if (mainImg && mainImg.image_url) {
        return mainImg.image_url;
    }
    return 'https://via.placeholder.com/150'; // Ảnh mặc định nếu lỗi
};

// 4. Xử lý Hủy đơn
const cancelOrder = async (orderId) => {
    if (!confirm('Bạn có chắc muốn hủy đơn hàng này?')) return;
    try {
        await api.post(`/cancel-order/${orderId}`);
        // Cập nhật UI ngay lập tức
        const order = orders.value.find(o => o.order_id === orderId);
        if (order) order.status = 'cancelled';
        alert('Đã hủy đơn hàng thành công');
    } catch (error) {
        alert('Lỗi khi hủy đơn hàng');
    }
};

// 5. Màu sắc trạng thái
const getStatusBadge = (status) => {
    const config = {
        pending: { text: 'CHỜ XÁC NHẬN', color: 'text-warning' },
        shipping: { text: 'ĐANG GIAO', color: 'text-primary' },
        completed: { text: 'HOÀN THÀNH', color: 'text-success' },
        cancelled: { text: 'ĐÃ HỦY', color: 'text-danger' },
    };
    return config[status] || { text: status, color: 'text-muted' };
};
const getFormattedColor = (hex) => {
    if (!hex) return 'transparent'; // Xử lý nếu không có dữ liệu màu
    // Kiểm tra nếu chuỗi đã có ký tự '#' thì trả về luôn
    if (String(hex).startsWith('#')) {
        return hex;
    }
    // Ngược lại, thêm '#' vào đầu chuỗi
    return '#' + hex;
};

const showReviewModal = ref(false);
const selectedProductToReview = ref(null);

// Hàm được gọi khi bấm nút "Đánh giá" ở list đơn hàng
const openReviewModal = (item) => {
    selectedProductToReview.value = {
        product_id: item.variant?.product?.product_id, // Lấy ID sản phẩm gốc
        name: item.variant?.product?.name,
        // Sử dụng lại hàm getProductImage có sẵn để lấy ảnh
        image_url: getProductImage(item),
        color: item.variant?.color,
        size: item.variant?.size,
        variant_id: item.variant_id // Nếu cần gửi cả variant_id
    };
    showReviewModal.value = true;
};
onMounted(() => {
    fetchOrders();
});
</script>

<template>
    <ModalReviewProduct
            v-model="showReviewModal"
            :product="selectedProductToReview"
        />
    <div class="order-history-page bg-light min-vh-100 pb-3">
        <div class="container">

            <div class="bg-white sticky-top shadow-sm mb-3 rounded-top">
                <ul class="nav nav-tabs nav-fill flex-nowrap overflow-auto no-scrollbar">
                    <li class="nav-item" v-for="tab in tabs" :key="tab.id">
                        <a
                            class="nav-link py-3 fw-medium text-nowrap cursor-pointer"
                            :class="{ 'active-tab': activeTab === tab.id }"
                            @click="selectTab(tab.id)"
                        >
                            {{ tab.label }}
                            </a>
                    </li>
                </ul>
            </div>

            <div class="bg-white p-3 mb-3 rounded shadow-sm">
                <div class="input-group bg-light rounded border-0">
                    <span class="input-group-text bg-transparent border-0"><i class="bi bi-search text-muted"></i></span>
                    <input type="text" class="form-control bg-transparent border-0 shadow-none" placeholder="Tìm kiếm theo ID đơn hàng hoặc Tên sản phẩm" @change="">
                </div>
            </div>

            <div v-if="loading" class="text-center py-5">
                <div class="spinner-border text-primary" role="status"></div>
            </div>

            <div v-else-if="filteredOrders.length === 0" class="text-center py-5 bg-white rounded shadow-sm">
                <img src="https://deo.shopeemobile.com/shopee/shopee-pcmall-live-sg/5fafbb923393b712b96488590b8f781f.png" width="100" alt="Empty">
                <p class="mt-3 text-muted">Chưa có đơn hàng nào</p>
                <button class="btn btn-brand text-white mt-2">Mua sắm ngay</button>
            </div>

            <div v-else class="d-flex flex-column gap-3">
                <div
                    v-for="order in filteredOrders"
                    :key="order.order_id"
                    class="card border-0 shadow-sm rounded-3"
                >
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="fw-bold brand-text">Ocean Shop</span>
                            <span class="text-muted small border-start ps-2">ID: {{ order.order_id }}</span>
                        </div>
                        <span class="fw-bold small text-uppercase" :class="getStatusBadge(order.status).color">
                            {{ getStatusBadge(order.status).text }}
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <div
                            v-for="item in order.order_item"
                            :key="item.order_item_id"
                            class="p-3 border-bottom d-flex gap-3 item-row"
                        >
                            <div class="flex-shrink-0 border rounded overflow-hidden" style="width: 80px; height: auto;">
                                <img
                                    :src="'../../../../storage/' + getProductImage(item)"
                                    class="w-100 h-100 object-fit-cover"
                                    alt="Product Image"
                                >
                            </div>

                            <div class="flex-grow-1">
                                <h6 class="mb-1 text-truncate-2 text-dark">
                                    {{ item.variant?.product?.name || 'Sản phẩm không xác định' }}
                                </h6>
                                <div class="text-muted small mb-1">
                                    Phân loại: <span class="color-swatch" :style="{ backgroundColor: getFormattedColor(item.variant.color)}"></span>, {{ item.variant?.size }}
                                </div>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <span class="small">x{{ item.quantity }}</span>
                                    <span class="text-decoration-line-through text-muted small" v-if="item.price > 0">
                                        {{ formatCurrency(item.variant?.product?.price || 0) }}
                                    </span>
                                    <span class="fw-medium brand-text" v-if="item.price > 0">
                                        {{ formatCurrency(item.price) }}
                                    </span>
                                </div>
                            </div>
                            <template v-if="['completed', 'cancelled'].includes(order.status)">
                                <button v-if="order.status === 'completed'" @click="openReviewModal(item)" class="btn btn-outline-brand px-4 min-w-btn">Đánh giá</button>
                                <button class="btn btn-brand text-white px-4 min-w-btn">Mua lại</button>
                            </template>
                        </div>
                    </div>

                    <div class="card-footer bg-white border-top-0 pt-3 pb-4 px-3">
                        <div class="text-end mb-3">
                            <span class="text-muted small me-2">Thành tiền:</span>
                            <span class="fw-bold brand-text fs-5">{{ formatCurrency(order.final_amount) }}</span>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <button
                                v-if="order.status === 'pending'"
                                @click="cancelOrder(order.order_id)"
                                class="btn btn-secondary px-4 min-w-btn"
                            >
                                Hủy đơn
                            </button>

                            <button v-if="order.status === 'shipping'" class="btn btn-brand text-white px-4 min-w-btn">
                                Đã nhận hàng
                            </button>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
/* --- Config Color --- */
:root {
    --brand-color: #3497e0;
}
.brand-text { color: #3497e0 !important; }
.bg-brand { background-color: #3497e0 !important; }

/* --- Button Styles --- */
.btn-brand {
    background-color: #3497e0;
    border-color: #3497e0;
}
.btn-brand:hover {
    background-color: #287dbd;
    border-color: #287dbd;
}
.btn-outline-brand {
    color: #3497e0;
    border-color: #3497e0;
}
.btn-outline-brand:hover {
    background-color: rgba(52, 151, 224, 0.05);
}
.min-w-btn { min-width: 120px; }

/* --- Tab Styles (Shopee Like) --- */
.nav-tabs { border-bottom: 1px solid #f1f1f1; }
.nav-link {
    color: #555;
    border: none;
    border-bottom: 2px solid transparent;
    transition: all 0.2s;
    font-size: 0.95rem;
}
.nav-link:hover { color: #3497e0; }
.active-tab {
    color: #3497e0 !important;
    border-bottom: 2px solid #3497e0 !important;
}

/* --- Utilities --- */
.cursor-pointer { cursor: pointer; }
.object-fit-cover { object-fit: cover; }
.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
/* Hiệu ứng hover sản phẩm */
.item-row:hover { background-color: #fafafa; }
.color-swatch {
    width: 15px;
    height: 15px;
    border-radius: 50%;
    border: #3497e0 1px solid;
    display: inline-block;
}
</style>
