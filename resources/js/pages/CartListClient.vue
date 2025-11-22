<script setup>
import { ref, reactive, onMounted, computed } from 'vue';
import api from '../axios';

const carts = ref([])
const handleFetchCarts = async () => {
    try {
        const response = await api.get('/carts')
        if (response.status === 200) {
            carts.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách sản phẩm:', error)
    }
}
onMounted(() => {
    handleFetchCarts()
})

const handleIncreaseQuantity = (item) => {
    item.quantity++;
    // Thêm logic gọi API cập nhật giỏ hàng ở đây
}

const handleDecreaseQuantity = (item) => {
    if (item.quantity > 1) {
        item.quantity--;
        // Thêm logic gọi API cập nhật giỏ hàng ở đây
    }
}

const handleRemoveItem = (cartId) => {
    console.log('Xóa sản phẩm với cart_id:', cartId);
    // Thêm logic gọi API xóa sản phẩm khỏi giỏ hàng ở đây
}

const totalPrice = computed(() => {
    if (!carts.value.cart_item) return 0;
    return carts.value.cart_item.reduce((total, item) => {
        const price = item.variant.price > 0 ? item.variant.price : item.variant.product.price;
        return total + (price * item.quantity);
    }, 0);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

</script>
<template>
    <div class="container my-5">
        <h3 class="fw-bold mb-4"><i class="bi bi-cart3 me-2"></i>Giỏ hàng của bạn</h3>
        <div class="row">
            <!-- Cột danh sách sản phẩm -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll">
                            <label class="form-check-label fw-medium" for="selectAll">
                                Chọn tất cả ({{ carts.cart_item ? carts.cart_item.length : 0 }} sản phẩm)
                            </label>
                        </div>
                        <button class="btn btn-sm btn-outline-danger border-0">
                            <i class="bi bi-trash me-1"></i> Xóa mục đã chọn
                        </button>
                    </div>
                    <div class="card-body">
                        <div v-if="carts.cart_item && carts.cart_item.length > 0" class="cart-list">
                            <div v-for="cart in carts.cart_item" :key="cart.cart_id"
                                class="cart-item row align-items-center py-3 border-bottom">
                                <div class="col-1 text-center">
                                    <input class="form-check-input" type="checkbox">
                                </div>
                                <div class="col-2">
                                    <img :src="'../../../storage/' + cart.variant.image[0].image_url"
                                        alt="product-image" class="img-fluid rounded">
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-1">{{ cart.variant.product.name }}</h6>
                                    <small class="text-muted mb-1">
                                        Phân loại:
                                        <span class="border px-2 py-1 rounded-5">
                                            {{ cart.variant.size }},
                                            <span class="d-inline-block align-middle rounded-circle border"
                                                :style="{ backgroundColor: cart.variant.color, width: '15px', height: '15px' }"></span>
                                            <i class="bi bi-chevron-down fw-bold ms-1 mt-2"></i>
                                        </span>

                                    </small>
                                    <small class="d-block mt-3">
                                        Số lượng:
                                        <span class="border px-1 py-1 rounded-5">
                                            <button class="btn-change-stock" @click="handleDecreaseQuantity(cart)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <span class="fw-medium mx-2">
                                                {{ cart.quantity }}
                                            </span>
                                            <button class="btn-change-stock"  @click="handleIncreaseQuantity(cart)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </span>
                                    </small>

                                </div>
                                <div class="col-md-3"></div>
                                <div class="col-2 text-end">
                                    <button class="btn btn-sm btn-outline-danger border-0"
                                        @click="handleRemoveItem(cart.cart_id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <div class=" text-danger mt-3">
                                        {{ formatCurrency(cart.variant.price > 0 ? cart.variant.price :
                                            cart.variant.product.price) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="text-center p-5">
                            <i class="bi bi-cart-x" style="font-size: 4rem; color: #ccc;"></i>
                            <p class="mt-3 text-muted">Giỏ hàng của bạn đang trống.</p>
                            <router-link to="/products" class="btn btn-primary">Tiếp tục mua sắm</router-link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cột tổng kết -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-3 position-sticky" style="top: 20px;">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Tổng kết đơn hàng</h5>
                        <div class="mb-3">
                            <label for="voucher" class="form-label">Mã giảm giá</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="voucher" placeholder="Nhập mã giảm giá">
                                <button class="btn btn-outline-primary" type="button">Áp dụng</button>
                            </div>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-medium">{{ formatCurrency(totalPrice) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Phí vận chuyển:</span>
                            <span class="fw-medium">{{ formatCurrency(0) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span class="text-primary">{{ formatCurrency(totalPrice) }}</span>
                        </div>
                        <div class="d-grid mt-4">
                            <button class="btn btn-primary btn-lg">
                                Thanh toán
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.cart-item:last-child {
    border-bottom: none !important;
}
.btn-change-stock {
    background-color: transparent;
    border: none;
    cursor: pointer
}
.btn-change-stock:active > i {
    color: #3497E0;
}
</style>
