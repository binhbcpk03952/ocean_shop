<script setup>
import { useRoute } from 'vue-router';
const route = useRoute()

const isAcivePath = (path) => {
    return route.path === path
}
</script>
<template>
    <div class="col-lg-3">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
            <div class="list-group list-group-flush">

                <router-link to="/profile"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-dark"
                    :class="{ active_item : isAcivePath('/profile') }">
                    <i class="bi bi-person-circle fs-5"></i>
                    <span>Thông tin tài khoản</span>
                </router-link>

                <a href="#" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-dark ">
                    <i class="bi bi-bag-check fs-5"></i>
                    <span>Lịch sử mua hàng</span>
                </a>

                <router-link to="/profile/address"
                :class="{ active_item : isAcivePath('/profile/address') }"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-dark">
                    <i class="bi bi-geo-alt fs-5"></i>
                    <span>Sổ địa chỉ</span>
                </router-link>

                <a href="#" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-dark">
                    <i class="bi bi-heart fs-5"></i>
                    <span>Sản phẩm yêu thích</span>
                    <span class="badge bg-danger ms-auto rounded-pill">3</span>
                </a>

                <a href="#" class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-dark">
                    <i class="bi bi-ticket-perforated fs-5"></i>
                    <span>Kho Voucher</span>
                </a>

                <a href="#"
                    class="list-group-item list-group-item-action py-3 px-4 d-flex align-items-center gap-3 text-danger">
                    <i class="bi bi-box-arrow-right fs-5"></i>
                    <span>Đăng xuất</span>
                </a>

            </div>
        </div>
    </div>
</template>
<style scoped>
/* Màu chủ đạo */
:root {
  --brand-color: #3497e0;
}

.bg-light {
  background-color: #f4f6f8 !important;
}

/* Sidebar Menu Styles */
.list-group-item {
  border: none;
  color: #555;
  transition: all 0.2s;
}

.list-group-item:hover {
  color: #3497e0;
  background-color: #f8faff;
}

/* Item đang Active (đang ở trang Profile) */
.active_item {
  color: #3497e0 !important;
  font-weight: 600;
  background-color: transparent;
  position: relative;
}
/* Thêm dấu gạch đứng nhỏ bên phải để báo hiệu active */
.active_item::after {
  content: '';
  position: absolute;
  right: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 4px;
  height: 20px;
  background-color: #3497e0 !important;
  border-radius: 4px 0 0 4px;
}

/* Form Styles */
.text-end-sm {
  text-align: right;
}

.form-control:focus {
  border-color: #3497e0;
  box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.15);
}

.form-check-input:checked {
  background-color: #3497e0;
  border-color: #3497e0;
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

/* Responsive adjustment */
@media (max-width: 576px) {
  .text-end-sm {
    text-align: left !important;
    margin-bottom: 5px;
  }
  .border-end-md {
    border-right: none !important;
    border-bottom: 1px solid #eee;
    padding-bottom: 20px;
    margin-bottom: 20px;
  }
}
</style>
