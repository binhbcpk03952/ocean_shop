import { reactive } from 'vue'
import api from '../axios'

export const authState = reactive({
    loggedIn: false,
    user: null,
    role: null,
    email: null,
    token: null,
    initialized: false,
})

export const checkLogin = async () => {
    try {
        const res = await api.get('user')  // API phải yêu cầu login
        if (res.status === 200 && res.data)

            // Nếu thành công → cập nhật đúng user
            authState.loggedIn = true
        authState.user = res.data.name
        authState.role = res.data.role
        authState.email = res.data.email

    } catch (e) {
        // Nếu lỗi (401, 419, token hết hạn…) → Đặt loggedIn = false
        authState.loggedIn = false
        authState.user = null
        authState.role = null
        authState.token = null
    } finally {
        authState.initialized = true
    }
}
