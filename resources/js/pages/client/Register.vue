<script setup>
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
})

const register = async () => {
    try {
        // 1. Lấy CSRF token
        await axios.get('/sanctum/csrf-cookie')

        // 2. Gửi yêu cầu đăng ký
        await axios.post('/api/register', form.value)

        // 3. Nếu thành công
        alert('Đăng ký thành công!')
        router.push('/login')
        console.log(form.value);

    } catch (error) {
        if (error.response) {
            if (error.response.status === 422) {
                const errors = error.response.data.errors
                let errorMessage = 'Lỗi kiểm tra dữ liệu:\n'
                for (const field in errors) {
                    if (errors.hasOwnProperty(field)) {
                        errorMessage += `- ${field}: ${errors[field][0]}\n`
                    }
                }
                alert(errorMessage)

            } else {
                alert(`Lỗi Server (${error.response.status}): ${error.response.data.message || 'Có lỗi xảy ra từ server.'}`)
            }
        } else {
            alert('Lỗi kết nối: Vui lòng kiểm tra kết nối mạng của bạn.')
        }
        console.error(error)
    }
}
</script>
<template>
    <div class="register-wrapper d-flex align-items-center justify-content-center mt-3">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">

                            <div class="col-lg-6 d-none d-lg-block bg-image position-relative">
                                <div class="bg-overlay p-5 d-flex flex-column justify-content-end h-100">
                                    <h2 class="text-white fw-bold display-6">New Collection 2025</h2>
                                    <p class="text-white-50">Đăng ký ngay để nhận voucher giảm giá 20% cho đơn hàng đầu
                                        tiên.</p>
                                </div>
                            </div>

                            <div class="col-lg-6 p-5 bg-white position-relative">
                                <div class="circle-decor"></div>

                                <div class="text-center mb-4">
                                    <h3 class="fw-bold brand-color text-uppercase ls-2">Fashion Store</h3>
                                    <p class="text-muted small">Tạo tài khoản chỉ trong 30 giây</p>
                                </div>

                                <form @submit.prevent="handleRegister">
                                    <div class="form-floating mb-3">
                                        <input v-model="form.name" type="text" class="form-control rounded-3"
                                            id="floatingName" placeholder="Họ tên" required>
                                        <label for="floatingName"><i class="bi bi-person me-1"></i> Họ và tên</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input v-model="form.email" type="email" class="form-control rounded-3"
                                            id="floatingEmail" placeholder="Email" required>
                                        <label for="floatingEmail"><i class="bi bi-envelope me-1"></i> Email</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input v-model="form.password" type="password" class="form-control rounded-3"
                                            id="floatingPassword" placeholder="Mật khẩu" required>
                                        <label for="floatingPassword"><i class="bi bi-lock me-1"></i> Mật khẩu</label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input v-model="form.password_confirmation" type="password"
                                            class="form-control rounded-3" id="floatingConfirm" placeholder="Xác nhận"
                                            required>
                                        <label for="floatingConfirm"><i class="bi bi-check-circle me-1"></i> Nhập lại
                                            mật khẩu</label>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-custom w-100 py-3 fw-bold text-uppercase rounded-3 shadow-sm mt-2">
                                        Đăng ký thành viên
                                    </button>

                                    <div class="text-center mt-4">
                                        <span class="text-muted">Bạn đã có tài khoản? </span>
                                        <router-link to="/login"
                                            class="fw-bold text-decoration-none brand-color link-hover">
                                            Đăng nhập ngay
                                        </router-link>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
form {
    /* Thay đổi: dùng full width trong cột, giới hạn max-width để không quá rộng trên màn lớn */
    width: 100%;
    max-width: 480px;
    margin: 0 auto 40px auto;
    padding: 30px;
    box-shadow: 1px 2px 3px #0001;
    border-radius: 8px;
    background: transparent;
}

/* Đảm bảo vùng form-floating chiếm full width */
.form-floating {
    width: 100%;
}

/* Tăng kích thước/height của input để nhìn cân đối với khung */
.form-control {
    border: 1px solid #e0e0e0;
    background-color: #f8f9fa;
    padding: 0.85rem 1rem;
    font-size: 1rem;
    height: auto;
    min-height: 48px;
    box-sizing: border-box;
}

/* Giữ focus rõ ràng */
.form-control:focus {
    background-color: #fff;
    border-color: #3497e0;
    box-shadow: 0 0 0 4px rgba(52, 151, 224, 0.12);
}

/* Khoảng cách giữa các trường lớn hơn */
.form-floating+.form-floating {
    margin-top: 0.75rem;
}

/* Responsive: trên màn nhỏ giảm padding và max-width */
@media (max-width: 767.98px) {
    form {
        padding: 20px;
        margin-top: 30px;
        max-width: 100%;
    }
}

:root {
    --primary-color: #3497e0;
    --primary-hover: #287dbd;
}

/* --- Layout & Background --- */
.register-wrapper {
    min-height: 100vh;
    background-color: #ffffff;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

/* --- Cột Hình Ảnh --- */
.bg-image {
    /* Ảnh thời trang lifestyle */
    background: url('https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=2070&auto=format&fit=crop') center center no-repeat;
    background-size: cover;
}

.bg-overlay {
    background: linear-gradient(to top, rgba(52, 151, 224, 0.8), transparent);
    /* Gradient màu xanh chủ đạo mờ dần */
}

/* --- Typography & Colors --- */
.brand-color {
    color: #3497e0;
}

.ls-2 {
    letter-spacing: 2px;
}

/* --- Custom Button --- */
.btn-custom {
    background-color: #3497e0;
    border-color: #3497e0;
    color: white;
    transition: all 0.3s ease;
}

.btn-custom:hover {
    background-color: #287dbd;
    border-color: #287dbd;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 151, 224, 0.3);
}

.link-hover:hover {
    text-decoration: underline !important;
    color: #287dbd;
}

/* --- Decorative Elements --- */
.circle-decor {
    position: absolute;
    top: -50px;
    right: -50px;
    width: 150px;
    height: 150px;
    background: rgba(52, 151, 224, 0.05);
    border-radius: 50%;
    z-index: 0;
    pointer-events: none;
}
</style>
