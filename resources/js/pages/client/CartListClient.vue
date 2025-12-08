<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue';
import api from '../../axios';
import ModalChangeVariant from '../../components/client/ModalChangeVariant.vue';
import ConfirmBox from '../../components/box_alert/ConfirmBox.vue';
import { emitter } from '../../stores/eventBus';
import { useRouter, useRoute } from 'vue-router';

const router = useRouter();
const route = useRoute();


const carts = ref([])
const loadingItemIds = ref(new Set()); // Track which items are loading
const productsSelected = ref([]);
const productsSelectedAll = ref(false)
//modal xoá sản phẩm trong giỏ hàng
const showBox = ref(false);
const message = ref('');
const selectedIdConfirm = ref(null);
const cartIdToDelete = ref(null);

const closeBox = () => {
    showBox.value = false;
    selectedIdConfirm.value = false;
}
const activeConfirm = () => {
    showBox.value = false;
    selectedIdConfirm.value = true;
    if (cartIdToDelete.value) {
        handleRemoveItem(cartIdToDelete.value)
        // console.log(cartIdToDelete.value)
    }
    cartIdToDelete.value = null;
}
const deleteCart = (cart_item_id) => {
    cartIdToDelete.value = cart_item_id;
    showBox.value = true
    message.value = `Bạn chắc chắn muốn xoá sản phẩm này khỏi giỏ hàng?`
}

// modal chỉnh sửa sản phẩm
const openModal = ref(false);
const selectedProductId = ref(null)
const selectedColor = ref(null)
const selectesSize = ref(null)
const cartItemId = ref(null)
const handleOpenModal = (cart) => {
    selectedProductId.value = cart.variant.product_id;
    cartItemId.value = cart.cart_item_id
    selectedColor.value = cart.variant.color;
    selectesSize.value = cart.variant.size;
    openModal.value = true;
}
const handleCloseModal = () => {
    openModal.value = false;
    selectedProductId.value = null;
    selectedColor.value = null;
    selectesSize.value = null;
    cartItemId.value = null
}

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

// Helper: Check if item is loading
const isItemLoading = (cartItemId) => {
    return loadingItemIds.value.has(cartItemId);
}

const handleIncreaseQuantity = async (item) => {
    const itemId = item.cart_item_id;

    // Optimistic update: cập nhật UI ngay lập tức
    const oldQuantity = item.quantity;
    item.quantity++;

    // Đánh dấu item đang loading
    loadingItemIds.value.add(itemId);

    try {
        const response = await api.put(`/carts/${itemId}`, {
            quantity: item.quantity
        })

        if (response.status === 200 && response.data.status) {
            // Success - UI đã cập nhật, chỉ cần xóa loading flag
        } else {
            // Rollback nếu API trả về lỗi
            item.quantity = oldQuantity;
            alert('Chưa thể cập nhật số lượng giỏ hàng ngay bây giờ')
        }
    } catch (err) {
        // Rollback nếu API gọi thất bại
        item.quantity = oldQuantity;
        console.error('Đã có lỗi xảy ra khi gọi API:', err);
        alert('Lỗi cập nhật giỏ hàng');
    } finally {
        // Xóa loading flag
        loadingItemIds.value.delete(itemId);
    }
}

const handleDecreaseQuantity = async (item) => {
    const itemId = item.cart_item_id;

    if (item.quantity <= 1) {
        alert('Số lượng không thể ít hơn 1');
        return;
    }

    // Optimistic update
    const oldQuantity = item.quantity;
    item.quantity--;

    loadingItemIds.value.add(itemId);

    try {
        const response = await api.put(`/carts/${itemId}`, {
            quantity: item.quantity // Fix: gửi quantity-1 chứ không phải quantity+1
        })

        if (response.status === 200 && response.data.status) {
            // Success
        } else {
            // Rollback
            item.quantity = oldQuantity;
            alert('Chưa thể cập nhật số lượng giỏ hàng ngay bây giờ')
        }
    } catch (err) {
        // Rollback
        item.quantity = oldQuantity;
        console.error('Đã có lỗi xảy ra khi gọi API:', err);
        alert('Lỗi cập nhật giỏ hàng');
    } finally {
        loadingItemIds.value.delete(itemId);
    }
}

const handleRemoveItem = async (cartId) => {
    try {
        const response = await api.delete(`/carts/${cartId}`);
        if (response.status === 200 && response.data.status) {
            // Remove from UI
            carts.value = {
                ...carts.value,
                cart_item: carts.value.cart_item.filter(
                    item => item.cart_item_id !== cartId
                )
            };
            productsSelected.value = productsSelected.value.filter(
                id => id !== cartId
            );
            emitter.emit('update_stock-cart')
        } else {
            alert('Chưa thể xóa sản phẩm');
        }
    } catch (err) {
        console.error('Lỗi xóa sản phẩm:', err);
        alert('Lỗi xóa sản phẩm');
    }
}


const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const handleUpdateVariant = async (data) => {
    try {
        const res = await api.patch(`/carts/${cartItemId.value}`, {
            variant_id: data.variant_id
        });
        if (res.status === 200) {
            await handleFetchCarts();
            handleCloseModal();
        }
    } catch (err) {
        console.error("Cập nhật thất bại");

    }
};

const priceSelector = computed(() => {
    if (!carts.value.cart_item) return 0;
    return carts.value.cart_item
        .filter(item => productsSelected.value.includes(item.cart_item_id))
        .reduce((total, item) => {
            const price = item.variant.price > 0 ? item.variant.price : item.variant.product.price;
            return total + (price * item.quantity);
        }, 0);
});
const toogleSelectedAll = () => {
    if (!productsSelectedAll.value) {
        productsSelected.value = carts.value.cart_item.map(item => item.cart_item_id);
    } else {
        productsSelected.value = [];
    }
}
const handleToCheckout = () => {
    if (productsSelected.value.length === 0) {
        alert('Vui lòng chọn sản phẩm để thanh toán');
        return;
    }
    // Chuyển hướng đến trang thanh toán với các sản phẩm đã chọn
    router.push({
        path: '/checkout', // Hoặc name: 'Checkout' tuỳ theo router của bạn
        query: {
            products: JSON.stringify(productsSelected.value)
        }
    });

}

watch(productsSelected, (newSelected) => {
    if (carts.value.cart_item) {
        productsSelectedAll.value = newSelected.length === carts.value.cart_item.length;
    } else {
        productsSelectedAll.value = false;
    }
});

</script>

<template>
    <confirm-box :show="showBox" :message="message" @cancel="closeBox" @confirm="activeConfirm" />
    <modal-change-variant :open-modal="openModal" :productId="selectedProductId" :color="selectedColor"
        :size="selectesSize" @close="handleCloseModal" @save="handleUpdateVariant" />
    <div class="container my-5">
        <h3 class="fw-bold mb-4 color-main"><i class="bi bi-cart3 me-2"></i>Giỏ hàng của bạn</h3>
        <div class="row">
            <!-- Cột danh sách sản phẩm -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-3">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAll" v-model="productsSelectedAll"
                                @click="toogleSelectedAll">
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
                            <div v-for="cart in carts.cart_item" :key="cart.cart_item_id + '-' + cart.variant.id"
                                class="cart-item row align-items-center py-3 border-bottom">
                                <div class="col-1 text-center">
                                    <input class="form-check-input" type="checkbox" :value="cart.cart_item_id"
                                        v-model="productsSelected">
                                </div>
                                <div class="col-2">
                                    <img :src="'../../../storage/' + cart.variant.image[0].image_url"
                                        alt="product-image" class="img-fluid rounded">
                                </div>
                                <div class="col-4">
                                    <h6 class="mb-1">{{ cart.variant.product.name }}</h6>
                                    <small class="text-muted mb-1">
                                        Phân loại:
                                        <span class="border px-2 py-1 rounded-5" style="cursor: pointer;"
                                            @click="handleOpenModal(cart)">
                                            {{ cart.variant.size }},
                                            <span class="d-inline-block align-middle rounded-circle border"
                                                :style="{ backgroundColor: cart.variant.color, width: '15px', height: '15px' }"></span>
                                            <i class="bi bi-chevron-down fw-bold ms-1 mt-2"></i>
                                        </span>
                                    </small>
                                    <small class="d-block mt-3">
                                        Số lượng:
                                        <span class="border px-1 py-1 rounded-5">
                                            <button class="btn-change-stock" @click="handleDecreaseQuantity(cart)"
                                                :disabled="isItemLoading(cart.cart_item_id)">
                                                <i class="bi bi-dash"></i>
                                            </button>
                                            <span class="fw-medium mx-2">
                                                {{ cart.quantity }}
                                            </span>
                                            <button class="btn-change-stock" @click="handleIncreaseQuantity(cart)"
                                                :disabled="isItemLoading(cart.cart_item_id)">
                                                <i class="bi bi-plus"></i>
                                            </button>
                                        </span>
                                    </small>
                                </div>
                                <div class="col-md-3"></div>
                                <div class="col-2 text-end">
                                    <button class="btn btn-sm btn-outline-danger border-0"
                                        @click="deleteCart(cart.cart_item_id)"
                                        :disabled="isItemLoading(cart.cart_item_id)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                    <div class="text-danger mt-3">
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

                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Tạm tính:</span>
                            <span class="fw-medium">{{ formatCurrency(priceSelector) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Giảm giá:</span>
                            <span class="fw-medium">{{ formatCurrency(0) }}</span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between fw-bold fs-5">
                            <span>Tổng cộng:</span>
                            <span class="text-primary">{{ formatCurrency(priceSelector) }}</span>
                        </div>
                        <div class="d-grid mt-4">
                            <button class="btn bg-main text-white btn-lg"
                                :disabled="productsSelected.length === 0 || carts.cart_item.length === 0"
                                @click="handleToCheckout">
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
    cursor: pointer;
    transition: color 0.2s;
}

.btn-change-stock:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-change-stock:active>i {
    color: #3497E0;
}
</style>
