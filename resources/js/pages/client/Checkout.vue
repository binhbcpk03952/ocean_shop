<script setup>
import { reactive, computed } from 'vue';

// 1. Dữ liệu Form
const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  city: '',
  district: '',
  ward: '',
  note: '',
  paymentMethod: 'cod'
});

// 2. Dữ liệu Giỏ hàng (Mock Data)
const cartItems = reactive([
  {
    id: 1,
    name: 'Áo Polo Nam Coolmax',
    color: 'Xanh Navy',
    size: 'L',
    price: 299000,
    quantity: 2,
    image: 'https://images.unsplash.com/photo-1617127365659-c47fa864d8bc?q=80&w=150&auto=format&fit=crop'
  },
  {
    id: 2,
    name: 'Quần Jeans Slimfit',
    color: 'Đen',
    size: '31',
    price: 450000,
    quantity: 1,
    image: 'https://images.unsplash.com/photo-1542272454315-4c01d7abdf4a?q=80&w=150&auto=format&fit=crop'
  }
]);

// 3. Tính toán tiền
const subtotal = computed(() => {
  return cartItems.reduce((sum, item) => sum + item.price * item.quantity, 0);
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

const handleOrder = () => {
  // Validate sơ bộ
  if(!form.city || !form.district) {
    alert("Vui lòng chọn đầy đủ địa chỉ!");
    return;
  }

  const orderData = {
    customer: form,
    items: cartItems,
    total: total.value
  };

  console.log('Đặt hàng thành công:', orderData);
  alert(`Cảm ơn ${form.name}! Đơn hàng của bạn đã được ghi nhận.`);
};
</script>
<template>
  <div class="checkout-page py-5 bg-light min-vh-100">
    <div class="container">
      <form @submit.prevent="handleOrder">
        <div class="row g-5">

          <div class="col-lg-7">
            <div class="bg-white p-4 rounded-4 shadow-sm mb-4">
              <h4 class="mb-4 fw-bold text-dark">Thông tin giao hàng</h4>

              <div class="row g-3">
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input v-model="form.name" type="text" class="form-control" id="floatingName" placeholder="Họ tên" required>
                    <label for="floatingName">Họ và tên người nhận</label>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-floating mb-3">
                    <input v-model="form.phone" type="tel" class="form-control" id="floatingPhone" placeholder="SĐT" required>
                    <label for="floatingPhone">Số điện thoại</label>
                  </div>
                </div>
              </div>

              <div class="form-floating mb-3">
                <input v-model="form.email" type="email" class="form-control" id="floatingEmail" placeholder="Email">
                <label for="floatingEmail">Email (để nhận hóa đơn)</label>
              </div>

              <div class="form-floating mb-3">
                <input v-model="form.address" type="text" class="form-control" id="floatingAddress" placeholder="Địa chỉ" required>
                <label for="floatingAddress">Địa chỉ (Số nhà, đường...)</label>
              </div>

              <div class="row g-3 mb-3">
                <div class="col-md-4">
                  <select v-model="form.city" class="form-select py-3" required>
                    <option value="" disabled selected>Chọn Tỉnh/Thành</option>
                    <option value="Hanoi">Hà Nội</option>
                    <option value="HCM">TP. Hồ Chí Minh</option>
                    <option value="DaNang">Đà Nẵng</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select v-model="form.district" class="form-select py-3" required>
                    <option value="" disabled selected>Chọn Quận/Huyện</option>
                    <option value="1">Quận 1</option>
                    <option value="2">Quận Cầu Giấy</option>
                  </select>
                </div>
                <div class="col-md-4">
                  <select v-model="form.ward" class="form-select py-3" required>
                    <option value="" disabled selected>Chọn Phường/Xã</option>
                    <option value="1">Phường Yên Hòa</option>
                    <option value="2">Phường Bến Nghé</option>
                  </select>
                </div>
              </div>

              <div class="form-floating">
                <textarea v-model="form.note" class="form-control" placeholder="Ghi chú" id="floatingNote" style="height: 100px"></textarea>
                <label for="floatingNote">Ghi chú đơn hàng (Tùy chọn)</label>
              </div>
            </div>

            <div class="bg-white p-4 rounded-4 shadow-sm">
              <h4 class="mb-4 fw-bold text-dark">Phương thức thanh toán</h4>

              <div class="payment-methods">
                <div
                  class="payment-item border rounded-3 p-3 mb-2 d-flex align-items-center cursor-pointer"
                  :class="{ 'active-method': form.paymentMethod === 'cod' }"
                  @click="form.paymentMethod = 'cod'"
                >
                  <input class="form-check-input me-3" type="radio" value="cod" v-model="form.paymentMethod">
                  <div class="d-flex flex-column">
                    <span class="fw-bold">Thanh toán khi nhận hàng (COD)</span>
                    <small class="text-muted">Bạn chỉ phải thanh toán khi nhận được hàng.</small>
                  </div>
                  <i class="bi bi-cash-coin ms-auto fs-4 text-secondary"></i>
                </div>

                <div
                  class="payment-item border rounded-3 p-3 d-flex align-items-center cursor-pointer"
                  :class="{ 'active-method': form.paymentMethod === 'banking' }"
                  @click="form.paymentMethod = 'banking'"
                >
                  <input class="form-check-input me-3" type="radio" value="banking" v-model="form.paymentMethod">
                  <div class="d-flex flex-column">
                    <span class="fw-bold">Chuyển khoản ngân hàng / VNPAY</span>
                    <small class="text-muted">Giảm ngay 5% tối đa 100k.</small>
                  </div>
                  <i class="bi bi-qr-code-scan ms-auto fs-4 text-secondary"></i>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 20px;">
              <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                <h4 class="fw-bold text-dark">Đơn hàng ({{ cartItems.length }} sản phẩm)</h4>
              </div>

              <div class="card-body px-4">
                <div class="cart-list mb-4">
                  <div v-for="item in cartItems" :key="item.id" class="d-flex align-items-center mb-3">
                    <div class="position-relative me-3">
                      <img :src="item.image" :alt="item.name" class="rounded border product-img">
                      <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-secondary border border-light">
                        {{ item.quantity }}
                      </span>
                    </div>

                    <div class="flex-grow-1">
                      <h6 class="mb-0 fw-bold text-dark">{{ item.name }}</h6>
                      <small class="text-muted">{{ item.color }} / {{ item.size }}</small>
                    </div>
                    <div class="fw-bold brand-color">
                      {{ formatCurrency(item.price * item.quantity) }}
                    </div>
                  </div>
                </div>

                <hr class="text-muted opacity-25">

                <div class="input-group mb-4">
                  <input type="text" class="form-control" placeholder="Nhập mã giảm giá">
                  <button class="btn btn-outline-secondary" type="button">Áp dụng</button>
                </div>

                <hr class="text-muted opacity-25">

                <div class="d-flex justify-content-between mb-2">
                  <span class="text-muted">Tạm tính</span>
                  <span class="fw-bold">{{ formatCurrency(subtotal) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-3">
                  <span class="text-muted">Phí vận chuyển</span>
                  <span v-if="shippingFee === 0" class="text-success fw-bold">Miễn phí</span>
                  <span v-else>{{ formatCurrency(shippingFee) }}</span>
                </div>

                <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                  <span class="h5 fw-bold mb-0">Tổng cộng</span>
                  <span class="h4 fw-bold brand-color mb-0">{{ formatCurrency(total) }}</span>
                </div>
              </div>

              <div class="card-footer bg-white border-top-0 p-4 pt-0">
                <button type="submit" class="btn btn-brand w-100 py-3 text-uppercase fw-bold rounded-3 shadow-sm fs-5">
                  Đặt hàng ngay
                </button>
                <div class="text-center mt-3">
                  <router-link to="/cart" class="text-decoration-none brand-link small">
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
  background-color: #f4f6f8 !important; /* Màu nền xám nhạt đặc trưng của các trang checkout */
}

/* Custom Input Focus */
.form-control:focus, .form-select:focus {
  border-color: #3497e0;
  box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.25);
}

/* Ảnh sản phẩm thumbnail */
.product-img {
  width: 64px;
  height: 64px;
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
</style>
