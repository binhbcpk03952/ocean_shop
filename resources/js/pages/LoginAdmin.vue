<script setup>
import { reactive, inject } from 'vue';
import api from '../axios';
import { useRouter } from 'vue-router';

const router = useRouter();
const authStore = inject('auth')
const auth = authStore.auth
const checkLogin = authStore.checkLogin

const form = reactive({
    email: '',
    password: '',
});

const handleLogin = async () => {
    try {
        const res = await api.post('/login', {
            email: form.email,
            password: form.password,
        });

        if (res.status === 200) {

            // ⭐ Cập nhật auth ngay tại chỗ
            auth.loggedIn = true
            auth.user = res.data.user.name   // dùng user, không phải name
            auth.role = res.data.user.role
            auth.token = res.data.token

            // ⭐ Lưu vào localStorage để Header load được
            localStorage.setItem('auth_token', res.data.token)
            localStorage.setItem('user_data', JSON.stringify({
                name: res.data.user.name,
                role: res.data.user.role
            }))

            // ⭐ Đồng bộ trạng thái với server (không bắt buộc)
            await checkLogin()

            console.log("Đăng nhập thành công!", auth)

            router.push('/')
        }

    } catch (error) {
        console.error('Lỗi đăng nhập:', error.response?.data?.message);
        alert(error.response?.data?.message || "Lỗi đăng nhập!");
    }
};
</script>


<template>
    <div class="container mt-5">
        <form @submit.prevent="handleLogin">
            <h2>Đăng nhập</h2>
            <div class="mb-3">
                <label>Email:</label>
                <input v-model="form.email" class="form-control" type="email" required />
            </div>
            <div class="mb-3">
                <label>Mật khẩu:</label>
                <input v-model="form.password" class="form-control" type="password" required />
            </div>
            <button class="btn btn-primary">Đăng nhập</button>
            <div class="text-end">
                <span>Bạn chưa có tài khoản? <router-link to="/register">Đăng ký</router-link></span>
            </div>
        </form>
    </div>
</template>
<style scoped>
form {
    width: 50%;
    margin: 0px auto;
    margin-bottom: 201px;
    margin-top: 105px;
    box-shadow: 1px 2px 3px #0001;
    padding: 50px;
}
</style>
