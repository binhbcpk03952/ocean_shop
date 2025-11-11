<script setup>
import axios from 'axios'
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/authGlobal'

axios.defaults.withCredentials = true
axios.defaults.baseURL = 'http://127.0.0.1:8000/'

const router = useRouter()
const auth = useAuthStore()
const form = ref({ email: '', password: '' })

const login = async () => {
    try {
        const success = await auth.login(form.value)
        if (success) {
            alert('Đăng nhập thành công!')
            router.push('/')
        }
    } catch (err) {
        console.error(err)
        alert(err.response?.data?.message || 'Sai thông tin đăng nhập!')
    }
}
</script>


<template>
    <div class="container mt-5">
        <h2>Đăng nhập</h2>
        <form @submit.prevent="login">
            <div class="mb-3">
                <label>Email:</label>
                <input v-model="form.email" class="form-control" type="email" required />
            </div>
            <div class="mb-3">
                <label>Mật khẩu:</label>
                <input v-model="form.password" class="form-control" type="password" required />
            </div>
            <button class="btn btn-primary">Đăng nhập</button>
        </form>
        <div class="text-end">
            <span>Bạn chưa có tài khoản? <router-link to="/register">Đăng ký</router-link></span>
        </div>
    </div>
</template>
