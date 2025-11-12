<script setup>
import { reactive, ref } from 'vue';
import api from '../axios'; // Giả sử file api.js của bạn nằm trong thư mục services
import { useRouter } from 'vue-router';
const router = useRouter();
const form = reactive({
  email: '',
  password: '',
});


const handleLogin = async () => {
  try {
    const response = await api.post('/login', {
      email: form.email,
      password: form.password,
    });

      if (response.status === 200) {
          const token = response.data.token;
          const user = response.data.user;

          localStorage.setItem('auth_token', token);
          localStorage.setItem('user_data', JSON.stringify(user));

          // Cập nhật trạng thái ứng dụng (ví dụ: dùng Vuex/Pinia)
          // Hoặc chuyển hướng người dùng đến trang Dashboard
          console.log('Đăng nhập thành công!', user);
          router.push('/');

    }

    // 1. LƯU TOKEN VÀ THÔNG TIN NGƯỜI DÙNG VÀO LOCAL STORAGE

  } catch (error) {
    console.error('Lỗi đăng nhập:', error.response.data.message);
    alert(error.response.data.message);
  }
};
</script>

<template>
    <div class="container mt-5">
        <h2>Đăng nhập</h2>
        <form @submit.prevent="handleLogin">
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
