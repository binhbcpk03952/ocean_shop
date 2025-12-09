<script setup>
import AsideProfile from '../components/client/AsideProfile.vue';
</script>
<template>
    <div class="profile-page py-5 bg-light min-vh-100">
        <div class="container">
            <div class="row g-4">
                <AsideProfile />
                
                <div class="col-lg-9">
                    <router-view></router-view>
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
.active-item {
    color: #3497e0;
    font-weight: 600;
    background-color: transparent;
    position: relative;
}

/* Thêm dấu gạch đứng nhỏ bên phải để báo hiệu active */
.active-item::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background-color: #3497e0;
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
