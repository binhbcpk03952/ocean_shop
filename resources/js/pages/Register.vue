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
    <div class="container mt-5">
        <h2>Đăng ký</h2>
        <form @submit.prevent="register">
            <div class="mb-3">
                <label>Tên:</label>
                <input v-model="form.name" class="form-control" required />
            </div>
            <div class="mb-3">
                <label>Email:</label>
                <input v-model="form.email" type="email" class="form-control" required />
            </div>
            <div class="mb-3">
                <label>Mật khẩu:</label>
                <input v-model="form.password" type="password" class="form-control" required />
            </div>
            <div class="mb-3">
                <label>Xác nhận mật khẩu:</label>
                <input v-model="form.password_confirmation" type="password" class="form-control" required />
            </div>
            <button class="btn btn-success">Đăng ký</button>
        </form>
        <div class="text-end">
            <span>Bạn đã có tài khoản? <router-link to="/login">Đăng nhập</router-link></span>
        </div>
    </div>
</template>
