import { defineStore } from 'pinia'
import axios from '@/axios' // 👈 import file axios ở trên

export const useAuthStore = defineStore('auth', {
    state: () => ({
        loggedIn: false,
        user: null,
        role: null,
        name: null,
        token: null,
        loading: false,
    }),

    actions: {
        // 🟢 Gọi khi người dùng nhấn Đăng nhập
        async login(form) {
            this.loading = true
            try {
                // 1️⃣ Lấy CSRF cookie từ Sanctum
                await axios.get('/sanctum/csrf-cookie')

                // 2️⃣ Gửi thông tin đăng nhập
                const res = await axios.post('/api/login', form)

                // 3️⃣ Nếu login thành công, gọi lại /api/user để lấy thông tin
                await this.fetchUser()

                alert(res.data.message || 'Đăng nhập thành công!')
                return true
            } catch (err) {
                console.error(err)
                alert(err.response?.data?.message || 'Sai thông tin đăng nhập!')
                return false
            } finally {
                this.loading = false
            }
        },

        // 🔄 Tự động lấy thông tin user từ backend (nếu còn cookie session)
        async fetchUser() {
            try {
                const res = await axios.get('/api/user')
                this.loggedIn = true
                this.user = res.data
                this.role = res.data.role
                this.name = res.data.name
                this.token = res.data.token
            } catch (err) {
                // Nếu lỗi => chưa đăng nhập hoặc session hết hạn
                this.loggedIn = false
                this.user = null
                this.role = null
                this.name = null
                this.token = null
            }
        },

        // 🚪 Đăng xuất
        async logout() {
            try {
                await axios.post('/api/logout')
            } catch (err) {
                console.error('Logout error:', err)
            } finally {
                this.$reset()
            }
        }
    }
})
