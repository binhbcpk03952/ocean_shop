import axios from 'axios'

axios.defaults.baseURL = 'http://127.0.0.1:8000' // ⚙️ đổi theo server bạn
axios.defaults.withCredentials = true // 🔒 cho phép gửi cookie Sanctum

export default axios
