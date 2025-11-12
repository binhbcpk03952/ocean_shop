import { defineStore } from 'pinia'
import axios from '@/axios'

export const useAuthStore = defineStore('auth', {
    state: () => ({
        loggedIn: false,
        role: null,
        name: null,
        loading: false,
    }),

    actions: {
        // 🟢 Gọi khi người dùng nhấn Đăng nhập
        async login(form) {
            this.loading = true
            try {
                const res = await axios.post('/api/login', form)
                alert(res.data.message || 'Đăng nhập thành công!')
                this.loggedIn = true
                this.name = res.data.name
                this.role = res.data.role
                this.loading = false

                localStorage('name', this.name)
                localStorage('role', this.role)
                localStorage('loggedIn', this.loggedIn)
                return true
            } catch (err) {
                console.error('Login error:', err.response?.data?.message || err)
                alert(err.response?.data?.message || 'Sai thông tin đăng nhập!')
                return false
            } finally {
                this.loading = false
            }
        },
    },

    // 🚪 Đăng xuất
    async logout() {
        try {
            // Gửi request xóa session/cookie về Laravel backend
            await axios.post('/api/logout')
        } catch (err) {
            console.error('Logout error:', err)
            // Lỗi khi logout thường không quan trọng, vẫn reset state client
        } finally {
            // Xóa trạng thái trong Pinia store (loggedIn = false, user = null,...)
            this.$reset()
        }
    }
})
