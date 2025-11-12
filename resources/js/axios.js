// Ví dụ: Trong một file cấu hình Axios (e.g., api.js)

import axios from 'axios';

const api = axios.create({
  baseURL: 'http://127.0.0.1:8000/api', // Thay thế bằng URL API Laravel của bạn
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
});

// Thêm Interceptor để đính kèm Token vào MỌI yêu cầu
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token'); // Lấy token từ Local Storage
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

export default api;
