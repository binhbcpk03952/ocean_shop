<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import api from '../../axios';
import { useRoute, useRouter } from 'vue-router';
import CreateAddress from './CreateAddress.vue';
import ModalChangePaymentMethod from '../../components/client/ModalChangePaymentMethod.vue';

// route
const route = useRoute()
const router = useRouter()
// console.log(route.query.products);
const openModalAddAddress = ref(false)
const mode = ref('create')
const handleCloseModal = () => {
    openModalAddAddress.value = false
}
const handleSaveModal = () => {
    handleFetchAddress()
    openModalAddAddress.value = false
}
const handleOpenModal = () => {
    openModalAddAddress.value = true
}

// thay doi phuong thuc thanh toan
const openModalChangePaymentMethod = ref(false)
const methodId = ref('cod')
const paymentMe = reactive(
    {
        id: 'cod',
        method_name: 'Thanh toán khi nhận hàng',
        icon: 'bi bi-cash-coin',
    }
)
const closeModalPaymentMethod = () => {
    openModalChangePaymentMethod.value = false
}
const openModalPaymentMethod = () => {
    openModalChangePaymentMethod.value = true
}
const saveModalMethod = (method) => {
    console.log(method);

    paymentMe.id = method.id
    paymentMe.method_name = method.method_name
    paymentMe.icon = method.icon
    closeModalPaymentMethod()
    console.log(paymentMe.id);

}
// 1. Dữ liệu Form
const address = ref([]);
const productsInCart = ref([]);
const selectAddress = ref(null);
const form = reactive({
    addressId: '',
    note: '',
    paymentMethod: 'cod',
    products: []
});
const getFormattedColor = (hex) => {
    if (!hex) return 'transparent'; // Xử lý nếu không có dữ liệu màu
    // Kiểm tra nếu chuỗi đã có ký tự '#' thì trả về luôn
    if (String(hex).startsWith('#')) {
        return hex;
    }
    // Ngược lại, thêm '#' vào đầu chuỗi
    return '#' + hex;
};
const handleFetchAddress = async () => {
    try {
        const res = await api.get('/addresses')
        if (res.status === 200) {
            address.value = res.data
        }
    } catch (err) {
        console.log('Loi khi goi API: ', err);
    }
}
const handleFetchProductsInCart = async () => {
    try {
        const res = await api.get('/carts')
        if (res.status === 200) {
            productsInCart.value = res.data.cart_item || []
            // console.log(productsInCart.value);

        }
    } catch (err) {
        console.log('Loi khi goi API: ', err);
    }
}
const productsSelected = computed(() => {
    if (!productsInCart.value?.length) return [];
    if (!route.query.products) return productsInCart.value;
    let ids = [];
    try {
        ids = JSON.parse(route.query.products); // [1,4,2]
    } catch (err) {
        console.log('Lỗi parse query products:', err);
        return productsInCart.value;
    }
    return productsInCart.value.filter(item =>
        ids.includes(item.variant_id)
    );
});

// console.log(productsSelected.value);




const addressDefault = computed(() => {
    if (!address.value || !address.value.length) return null
    if (!selectAddress.value) {
        return address.value.find(add => add.is_default === 1) || address.value[0]
    }
    return address.value.find(addr => addr.address_id === selectAddress.value)
})

// 3. Tính toán tiền
const subtotal = computed(() => {
    if (!productsSelected.value?.length) return 0;

    return productsSelected.value.reduce((sum, item) => {
        const price =
            item.variant.price && item.variant.price > 0
                ? item.variant.price
                : item.variant.product.price;

        return sum + price * item.quantity;
    }, 0);
});


const shippingFee = computed(() => {
    return subtotal.value > 500000 ? 0 : 30000; // Miễn phí ship nếu đơn > 500k
});

const total = computed(() => {
    return subtotal.value + shippingFee.value;
});

// 4. Hàm tiện ích & Xử lý
const formatCurrency = (value) => {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(value);
};

const handleOrder = async () => {
    if (paymentMe.id === 'cod') {
        try {
            const res = await api.post('orders', {
                address_id: addressDefault.value.address_id,
                note: form.note,
                total_amount: subtotal.value,
                shipping_fee: shippingFee.value,
                payment_method: paymentMe.id,
                promotion_id: null,
                discount_amount: 0,

                products: productsSelected.value.map(item => ({
                    cart_item_id: item.cart_item_id,
                    variant_id: item.variant_id,
                    quantity: item.quantity,
                    price: item.variant?.price ?? item.variant?.product?.price
                }))
            })

            console.log('Đặt hàng thành công:', res.data)
            alert('Đặt hàng thành công:')
            router.push('orders_success')

        } catch (err) {
            console.log('Lỗi khi gọi API:', err.response?.data || err)
        }
    } else if (paymentMe.id === 'vnpay') {
        alert('Thanh toan VNPay')
        try {
            const res = await api.post('/vnpay_payment', {
                amount: subtotal.value
            })
            if (res.data.payment_url) {
                 console.log(res.data.payment_url);
                window.location.href = res.data.payment_url
            }
        } catch (err) {
            console.log('Loi khi goi APi: ', err);
        }
    }
}


onMounted(() => {
    handleFetchAddress();
    handleFetchProductsInCart();
})
</script>
<template>
    <CreateAddress :open-modal="openModalAddAddress" :mode="mode" @close="handleCloseModal"
        @save="handleSaveModal" />
    <ModalChangePaymentMethod :open-modal="openModalChangePaymentMethod" :method-id="paymentMe.id" @close="closeModalPaymentMethod" @save="saveModalMethod"/>
    <div class="checkout-page py-5 bg-light min-vh-100">
        <div class="container">
            <form @submit.prevent="handleOrder">
                <div class="row g-5 mb-5">
                    <div class="col-lg-7">
                        <div class="bg-white p-4 rounded-3 border mb-4">
                            <h4 class="mb-4 fw-medium text-dark">Thông tin giao hàng</h4>
                            <div class="row g-3">
                                <div class="list_address fs-5 fw-medium d-flex justify-content-between my-3" v-if="!addressDefault">
                                    <span> Bạn chưa có địa chỉ nhận hàng</span>
                                    <button type="button"
                                        class="btn bg-main text-white"
                                        @click="handleOpenModal"
                                    >
                                        Thêm địa chỉ
                                    </button>
                                </div>
                                <div class="list_address-default d-flex justify-content-between mb-3" v-else>
                                    <div class="condition">
                                        <div class="name_phone">
                                            <span class="recipient_name">{{ addressDefault.recipient_name }}</span> | {{
                                                addressDefault.recipient_phone }}
                                        </div>
                                        <div class="address_line">
                                            {{ addressDefault.address_line }}
                                        </div>
                                        <div class="address">
                                            {{ addressDefault.ward }}, {{ addressDefault.district }}, {{
                                                addressDefault.province }}
                                        </div>
                                    </div>
                                    <button class="change-address" type="button" @click="showAddress = true">Thay
                                        đổi</button>
                                </div>
                            </div>
                            <div class="form-floating">
                                <textarea v-model="form.note" class="form-control" placeholder="Ghi chú"
                                    id="floatingNote" style="height: 100px"></textarea>
                                <label for="floatingNote">Ghi chú đơn hàng (Tùy chọn)</label>
                            </div>
                        </div>
                        <div class="card-header bg-white border rounded-3 pt-4 p-4">
                            <h4 class="fw-medium text-dark mb-4">Chi tiết đơn hàng</h4>
                            <div class="cart-list mb-4">
                                <div v-for="item in productsSelected" :key="item.cart_item_id"
                                    class="d-flex align-items-center mb-3">
                                    <div class="position-relative me-3">
                                        <img :src="'../../../../storage/' + item.variant.image[0]?.image_url"
                                            :alt="item.variant.product.name" class="rounded border product-img">

                                    </div>

                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 fw-medium text-dark mb-1">{{ item.variant.product.name }}</h6>
                                        <small class="text-muted d-flex align-items-center">
                                            <span class="color-swatch me-1"
                                                :style="{ backgroundColor: getFormattedColor(item.variant.color) }">
                                            </span>
                                            <span>
                                                / {{ item.variant.size }}
                                            </span>

                                        </small>
                                        <div class="quantity mt-3">
                                            x {{ item.quantity }}
                                        </div>
                                    </div>
                                    <div class="fw-medium brand-color">
                                        {{ formatCurrency((item.variant.price || item.variant.product.price) *
                                            item.quantity) }}
                                    </div>
                                </div>
                            </div>
                        </div>



                    </div>
                    <div class="col-lg-5">
                        <div class="card border rounded-3 sticky-top" style="top: 20px;">


                            <div class="card-body px-4">
                                <div class="bg-white rounded-3">


                                    <div class="input-group mb-4">
                                        <button type="button"
                                            class="btn color-main border rounded-3 w-100 py-2 d-flex justify-content-between px-4">
                                            <span>
                                                <i class="bi bi-tags fs-5"></i>
                                                Chọn khuyến mại
                                            </span>
                                            <i class="bi bi-chevron-compact-right fs-5 text-black"></i>
                                        </button>
                                    </div>
                                    <div class="payment-methods">
                                        <div class="payment-item border rounded-3 mb-2 px-4 d-flex justify-content-between align-items-center cursor-pointer"
                                            @click="openModalPaymentMethod" style="cursor: pointer;">
                                            <div class="d-flex align-items-center color-main">
                                                <i :class="paymentMe.icon + ' fs-4 me-3 mt-1'"></i>
                                                <span class="">{{ paymentMe.method_name }}</span>
                                            </div>
                                            <i class="bi bi-chevron-compact-right fs-5 text-black"></i>
                                        </div>

                                        <!-- <div class="payment-item border rounded-3 p-3 d-flex align-items-center cursor-pointer"
                                            :class="{ 'active-method': form.paymentMethod === 'banking' }"
                                            @click="form.paymentMethod = 'banking'">
                                            <input class="form-check-input me-3" type="radio" value="banking"
                                                v-model="form.paymentMethod">
                                            <div class="d-flex flex-column">
                                                <span class="fw-bold">Chuyển khoản ngân hàng / VNPAY</span>
                                                <small class="text-muted">Giảm ngay 5% tối đa 100k.</small>
                                            </div>
                                            <i class="bi bi-qr-code-scan ms-auto fs-4 text-secondary"></i>
                                        </div> -->

                                    </div>
                                </div>
                                <hr class="text-muted opacity-25">



                                <hr class="text-muted opacity-25">

                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Tạm tính</span>
                                    <span class="fw-bold">{{ formatCurrency(subtotal) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted ">Giảm giá</span>
                                    <span class="fw-bold">{{ formatCurrency(0) }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-3">
                                    <span class="text-muted">Phí vận chuyển</span>
                                    <span v-if="shippingFee === 0" class="text-success fw-bold">Miễn phí</span>
                                    <span v-else>{{ formatCurrency(shippingFee) }}</span>
                                </div>

                                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                                    <span class="h5 fw-bold mb-0">Tổng cộng</span>
                                    <span class="h5 fw-bold brand-color mb-0">{{ formatCurrency(total) }}</span>
                                </div>
                            </div>

                            <div class="card-footer bg-white border-top-0 p-4 pt-0 rounded-3">
                                <button type="submit"
                                    class="btn btn-brand w-100 py-2 text-uppercase fw-bold rounded-5 shadow-sm ">
                                    Đặt hàng ngay
                                </button>
                                <div class="text-center mt-3">
                                    <router-link to="/carts" class="text-decoration-none brand-link small">
                                        <i class="bi bi-chevron-left"></i> Quay lại giỏ hàng
                                    </router-link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>
</template>



<style scoped>
/* Màu chủ đạo */
:root {
    --brand-color: #3497e0;
}

.brand-color {
    color: #3497e0 !important;
}

.bg-light {
    background-color: #fff !important;
    /* Màu nền xám nhạt đặc trưng của các trang checkout */
}

/* Custom Input Focus */
.form-control:focus,
.form-select:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.25);
}

/* Ảnh sản phẩm thumbnail */
.product-img {
    width: 100px;
    height: auto;
    object-fit: cover;
    background: #fff;
}

/* Payment Method Styling */
.payment-item {
    transition: all 0.2s;
    border: 1px solid #dee2e6;
}

.payment-item:hover {
    background-color: #f8f9fa;
}

/* Khi chọn phương thức thanh toán, viền đổi màu xanh */
.active-method {
    border-color: #3497e0 !important;
    background-color: rgba(52, 151, 224, 0.05) !important;
    position: relative;
}

/* Nút Đặt hàng */
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

.brand-link {
    color: #3497e0;
    transition: 0.2s;
}

.brand-link:hover {
    color: #287dbd;
    text-decoration: underline !important;
}

.recipient_name {
    font-size: 18px;
    font-weight: 500;
}

.change-address {
    height: 40px;
    border: none;
    background-color: transparent;
    color: #188754;
    font-weight: bold;
}

.custom-loading-modal {
    background-color: rgba(0, 0, 0, 0.5);
    /* làm mờ nền */
    position: fixed;
    inset: 0;
    /* full màn hình */
    z-index: 2000;
    /* cao hơn modal khác */
    display: flex;
    align-items: center;
    justify-content: center;
}

.color-swatch {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    border: #3497e0 1px solid;
    display: inline-block;
}
</style>
