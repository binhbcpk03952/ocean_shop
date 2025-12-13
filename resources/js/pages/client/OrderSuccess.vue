<script setup>
import { reactive, ref, onMounted, inject } from 'vue';
import api from '../../axios';

const { auth } = inject('auth');


const orderSuccess = ref(null)

onMounted(async () => {
    try {
        const res = await api.get('/orders/latest')
        if (res.status === 200) {
            orderSuccess.value = res.data.order
        }
    } catch (err) {
        console.log('Loi khi goi APi: ', err);
    }
})
// Helper format tiền tệ
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};
const formatDateTime = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);

  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const year = date.getFullYear();
  const hours = String(date.getHours()).padStart(2, '0');
  const minutes = String(date.getMinutes()).padStart(2, '0');

  // Trả về dạng: 15:30 - 05/12/2025
  return `${hours}:${minutes} - ${day}/${month}/${year}`;
};
</script>
<template>
    <div class="thank-you-page min-vh-100 pb-5 d-flex align-items-center justify-content-center">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8 col-lg-6">

                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="card-header bg-brand text-white text-center py-4 border-0 position-relative">
                            <div class="success-icon-bg">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <h2 class="fw-bold mb-1 position-relative z-1">Đặt hàng thành công!</h2>
                            <p class="mb-0 opacity-75 position-relative z-1">Cảm ơn bạn đã mua sắm tại Ocean Shop</p>
                        </div>

                        <div class="card-body p-4 p-md-5">

                            <div class="text-center mb-4">
                                <p class="text-muted">
                                    Xin chào <span class="fw-bold text-dark">{{ auth.user }}</span>, đơn hàng
                                    của bạn đã được tiếp nhận và đang trong quá trình xử lý. Chúng tôi sẽ gửi email xác
                                    nhận tới <span class="fw-bold brand-text">{{ auth.email }}</span> trong giây lát.
                                </p>
                            </div>

                            <div class="bg-light rounded-3 p-3 mb-4 border border-dashed">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="small text-muted">Mã đơn hàng</div>
                                        <div class="fw-bold text-dark">#{{ orderSuccess?.order_id }}</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="small text-muted">Ngày đặt</div>
                                        <div class="fw-bold text-dark">{{ formatDateTime(orderSuccess?.created_at) }}</div>
                                    </div>
                                    <div class="col-12 border-top my-2 opacity-25"></div>
                                    <div class="col-6">
                                        <div class="small text-muted">Phương thức thanh toán</div>
                                        <div class="fw-bold text-dark">{{ orderSuccess?.payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Thanh toán VN Pay' }}</div>
                                    </div>
                                    <div class="col-6 text-end">
                                        <div class="small text-muted">Tổng cộng</div>
                                        <div class="fw-bold brand-text fs-5">{{ formatCurrency(orderSuccess?.final_amount) }}</div>
                                    </div>
                                </div>
                            </div>
<!--
                            <div class="mb-4">
                                <h6 class="fw-bold text-uppercase small text-muted mb-3">
                                    <i class="bi bi-geo-alt-fill me-1 brand-text"></i> Địa chỉ nhận hàng
                                </h6>
                                <div class="d-flex align-items-start">
                                    <div class="flex-grow-1">
                                        <p class="mb-1 fw-bold">{{ order.shippingAddress.name }} - {{
                                            order.shippingAddress.phone }}</p>
                                        <p class="mb-0 text-muted small">{{ order.shippingAddress.fullAddress }}</p>
                                    </div>
                                </div>
                            </div> -->

                            <div class="d-flex flex-column gap-3">
                                <router-link to="/products"
                                    class="btn btn-brand w-100 py-3 rounded-3 fw-bold text-uppercase shadow-sm">
                                    <i class="bi bi-arrow-left me-2"></i> Tiếp tục mua sắm
                                </router-link>

                                <router-link to="/profile/orders"
                                    class="btn btn-outline w-100 py-3 rounded-3 fw-bold text-secondary">
                                    Xem chi tiết đơn hàng
                                </router-link>
                            </div>

                        </div>

                        <div class="card-footer bg-white border-top py-3 text-center">
                            <small class="text-muted">
                                Gặp vấn đề với đơn hàng? <a href="#"
                                    class="brand-text text-decoration-none fw-bold">Liên hệ hỗ trợ</a>
                            </small>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</template>



<style scoped>
/* Màu chủ đạo */
:root {
    --brand-color: #3497e0;
}

.bg-brand {
    background-color: #3497e0;
}

.brand-text {
    color: #3497e0;
}

.thank-you-page {
    background-color: #f4f6f8;
    /* Nền xám nhẹ làm nổi bật Card */
}

/* Hiệu ứng Icon nền */
.success-icon-bg {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 8rem;
    color: rgba(255, 255, 255, 0.15);
    /* Màu trắng mờ */
    z-index: 0;
}

/* Border nét đứt cho box thông tin */
.border-dashed {
    border-style: dashed !important;
    border-color: #dee2e6 !important;
}

/* Nút chính */
.btn-brand {
    background-color: #3497e0;
    border: 1px solid #3497e0;
    color: white;
    transition: all 0.3s;
}

.btn-brand:hover {
    background-color: #287dbd;
    border-color: #287dbd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 151, 224, 0.3);
}

/* Nút phụ (Outline) */
.btn-outline {
    background-color: transparent;
    border: 1px solid #e0e0e0;
    transition: all 0.2s;
}

.btn-outline:hover {
    border-color: #3497e0;
    color: #3497e0 !important;
    background-color: #fff;
}

/* Utility */
.z-1 {
    z-index: 1;
}
</style>
