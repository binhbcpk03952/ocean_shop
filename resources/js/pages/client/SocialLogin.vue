<script setup>
import { onMounted, inject } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../axios'

const route = useRoute()
const router = useRouter()
const authStore = inject('auth')
const auth = authStore.auth
const checkLogin = authStore.checkLogin

onMounted(async () => {
    const token = route.query.token

    if (!token) {
        alert('Đăng nhập Google thất bại!')
        return router.push('/login')
    }

    // ✅ Lưu token
    localStorage.setItem('auth_token', token)

    try {
        // ✅ Gọi API lấy user từ token
        const res = await api.get('/user', {
            headers: {
                Authorization: `Bearer ${token}`
            }
        })

        auth.loggedIn = true
        auth.user = res.data.name
        auth.role = res.data.role
        auth.token = token

        localStorage.setItem('user_data', JSON.stringify({
            name: res.data.name,
            role: res.data.role
        }))

        await checkLogin()

        // ✅ Đăng nhập xong → về trang chủ
        router.push('/')
    } catch (err) {
        console.error(err)
        alert('Token Google không hợp lệ!')
        router.push('/login')
    }
})
</script>

<template>
    <div class="d-flex justify-content-center align-items-center" style="height:100vh">
        <h3>Đang đăng nhập bằng Google...</h3>
    </div>
</template>
