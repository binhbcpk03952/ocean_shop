<script setup>
import { reactive, inject } from 'vue'
import api from '../../axios'
import { useRouter, useRoute } from 'vue-router'

const router = useRouter()
const route = useRoute()
const authStore = inject('auth')
const auth = authStore.auth
const checkLogin = authStore.checkLogin

const form = reactive({
    email: '',
    password: '',
    remember: false,
})

// ✅ LOGIN BÌNH THƯỜNG
const handleLogin = async () => {
    try {
        const res = await api.post('/login', {
            email: form.email,
            password: form.password,
        })

        if (res.status === 200) {
            auth.loggedIn = true
            auth.user = res.data.user.name
            auth.role = res.data.user.role
            auth.token = res.data.token

            localStorage.setItem('auth_token', res.data.token)
            localStorage.setItem('user_data', JSON.stringify({
                name: res.data.user.name,
                role: res.data.user.role
            }))

            await checkLogin()

            if (route.query.back) {
                router.push(route.query.back)
            } else {
                router.push('/')
            }
        }
    } catch (error) {
        console.error('Lỗi đăng nhập:', error.response?.data?.message)
        alert(error.response?.data?.message || "Lỗi đăng nhập!")
    }
}

// ✅ LOGIN GOOGLE
const loginWithGoogle = () => {
    window.location.href = 'http://127.0.0.1:8000/api/auth/google'
}
</script>

<template>
    <div class="login-wrapper d-flex align-items-center justify-content-center my-3">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10 col-lg-11">
                    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                        <div class="row g-0">

                            <div class="col-lg-6 d-none d-lg-block bg-login-image position-relative">
                                <div class="bg-overlay p-5 d-flex flex-column justify-content-end h-100">
                                    <h2 class="text-white fw-bold display-6">Welcome Back!</h2>
                                    <p class="text-white-50">
                                        Đăng nhập để theo dõi đơn hàng và nhận ưu đãi dành riêng cho bạn.
                                    </p>
                                </div>
                            </div>

                            <div class="col-lg-6 p-5 bg-white position-relative">

                                <div class="text-center mb-4">
                                    <h3 class="fw-bold brand-color ls-2 d-flex align-items-center justify-content-center">
                                        <img src="../../../../public/images/logo_ocean_mini.png" alt="logo"
                                            style="height: 40px; width: 40px;" />
                                        <span class="text-uppercase ms-1">Ocean</span>
                                    </h3>
                                    <p class="text-muted small">Vui lòng đăng nhập tài khoản</p>
                                </div>

                                <form @submit.prevent="handleLogin">
                                    <div class="form-floating mb-3">
                                        <input v-model="form.email" type="email" class="form-control rounded-3"
                                            id="loginEmail" placeholder="Email" required>
                                        <label for="loginEmail">
                                            <i class="bi bi-envelope me-1"></i> Email / Số điện thoại
                                        </label>
                                    </div>

                                    <div class="form-floating mb-3">
                                        <input v-model="form.password" type="password" class="form-control rounded-3"
                                            id="loginPassword" placeholder="Mật khẩu" required>
                                        <label for="loginPassword">
                                            <i class="bi bi-lock me-1"></i> Mật khẩu
                                        </label>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-4">
                                        <div class="form-check">
                                            <input v-model="form.remember" class="form-check-input" type="checkbox"
                                                id="rememberMe">
                                            <label class="form-check-label small text-muted" for="rememberMe">
                                                Ghi nhớ đăng nhập
                                            </label>
                                        </div>
                                        <a href="#" class="text-decoration-none small brand-color fw-bold link-hover">
                                            Quên mật khẩu?
                                        </a>
                                    </div>

                                    <button type="submit"
                                        class="btn btn-custom w-100 py-3 fw-bold text-uppercase rounded-3 shadow-sm">
                                        Đăng nhập
                                    </button>

                                    <div class="text-center my-4 position-relative">
                                        <hr class="text-muted opacity-25">
                                        <span
                                            class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted small">
                                            Hoặc
                                        </span>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-center mb-4">
                                        <button type="button"
                                            class="btn btn-outline-light border text-dark flex-grow-1"
                                            @click="loginWithGoogle">
                                            <i class="bi bi-google text-danger"></i>
                                            <span class="small fw-bold">Google</span>
                                        </button>

                                        <button type="button"
                                            class="btn btn-outline-light border text-dark flex-grow-1" disabled>
                                            <i class="bi bi-facebook text-primary"></i>
                                            <span class="small fw-bold">Facebook</span>
                                        </button>
                                    </div>

                                    <div class="text-center">
                                        <span class="text-muted">Bạn chưa có tài khoản? </span>
                                        <router-link to="/register"
                                            class="fw-bold text-decoration-none brand-color link-hover">
                                            Đăng ký miễn phí
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
    width: 100%;
    max-width: 480px;
    margin: 105px auto 40px;
    box-shadow: 1px 2px 3px #0001;
    padding: 30px;
    background: transparent;
    border-radius: 8px;
}

:root {
    --primary-color: #3497e0;
}

.login-wrapper {
    min-height: 100vh;
    background-color: #ffffff;
    font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
}

.bg-login-image {
    background: url('https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1974&auto=format&fit=crop') center top no-repeat;
    background-size: cover;
}

.bg-overlay {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.6), transparent);
}

.brand-color {
    color: #3497e0;
}

.ls-2 {
    letter-spacing: 2px;
}

.form-floating {
    width: 100%;
}

.form-control {
    border: 1px solid #e0e0e0;
    background-color: #f8f9fa;
    padding: 0.85rem 1rem;
    font-size: 1rem;
    min-height: 48px;
    border-radius: .375rem;
}

.form-control:focus {
    background-color: #fff;
    border-color: #3497e0;
    box-shadow: 0 0 0 4px rgba(52, 151, 224, 0.15);
}

.form-floating+.form-floating {
    margin-top: 0.75rem;
}

.form-check-input:checked {
    background-color: #3497e0;
    border-color: #3497e0;
}

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

@media (max-width: 767.98px) {
    form {
        padding: 20px;
        margin-top: 30px;
        max-width: 100%;
    }
}
</style>
