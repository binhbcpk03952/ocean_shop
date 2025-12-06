<script setup>
import { reactive, ref, onMounted } from 'vue';
import api from '../../axios';

const user = reactive({
    name: '',
    email: '',
    phone: '',
    gender: '',
})
const errors = reactive({
    name: '',
    email: '',
    phone: '',
})
const resetErrors = () => {
    errors.name = '';
    errors.email = '';
    errors.phone = '';
}


const validated = () => {
    let isValid = true;
    // Reset lỗi
    resetErrors();
    if (!user.name) {
        errors.name = 'Vui lòng nhập họ và tên.';
        isValid = false;
    }
    if (!user.email) {
        errors.email = 'Vui lòng nhập email.';
        isValid = false;
    } else if (!/\S+@\S+\.\S+/.test(user.email)) {
        errors.email = 'Email không hợp lệ.';
        isValid = false;
    }
    if (user.phone && !/^\d{10,15}$/.test(user.phone)) {
        errors.phone = 'Số điện thoại không hợp lệ.';
        isValid = false;
    }
    return isValid;
}

const handleFetchProfile = async () => {
    try {
        const res = await api.get('/user');
        if (res.status === 200) {
            user.name = res.data.name;
            user.email = res.data.email;
            user.phone = res.data.phone;
            user.gender = res.data.sex;
        }
    } catch (error) {
        console.error('Error fetching profile:', error);
    }
}

onMounted(() => {
    handleFetchProfile();
})
// Ảnh mặc định
const defaultAvatar = 'https://cdn-icons-png.flaticon.com/512/3135/3135715.png';



// Xử lý khi chọn ảnh mới (Preview ảnh ngay lập tức)
const handleAvatarChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        // Tạo URL blob để preview
        user.avatarPreview = URL.createObjectURL(file);
        console.log("File selected:", file.name);
    }
};

// Xử lý lưu form
const updateProfile = async () => {
    if (!validated()) {
        return;
    }
    const formData = new FormData();
    formData.append('name', user.name);
    formData.append('email', user.email);
    formData.append('phone', user.phone);
    formData.append('sex', user.gender);
    if (user.avatarPreview) {
        formData.append('avatar', user.avatarPreview);
    }
    try {
        const res = await api.post('/profile', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        if (res.status === 200) {
            alert("Cập nhật hồ sơ thành công!");
            handleFetchProfile();
        }
    } catch (error) {
        console.error('Error updating profile:', error);
        alert("Cập nhật hồ sơ thất bại!");
    }
};
</script>


<template>

    <div class="card border-0 shadow-sm rounded-4 h-100">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4 pb-0">
            <h5 class="fw-bold text-dark mb-1">Hồ sơ của tôi</h5>
            <p class="text-muted small">Quản lý thông tin hồ sơ để bảo mật tài khoản</p>
            <hr class="mt-3 opacity-25">
        </div>

        <div class="card-body px-4 py-3">
            <form @submit.prevent="updateProfile">
                <div class="row">

                    <div class="col-md-8 pe-md-5 border-end-md">
                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-3 col-form-label text-muted text-end-sm">Họ và tên</label>
                            <div class="col-sm-9">
                                <input v-model="user.name" type="text" class="form-control"
                                    placeholder="Nhập tên của bạn">
                                <div v-if="errors.name" class="text-danger small mt-1">{{ errors.name }}</div>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-3 col-form-label text-muted text-end-sm">Email</label>
                            <div class="col-sm-9">
                                <div class="input-group">
                                    <input v-model="user.email" type="email" class="form-control"
                                        placeholder="email@example.com">
                                    <button class="btn btn-outline-secondary" type="button">Xác thực</button>
                                </div>
                                <div v-if="errors.email" class="text-danger small mt-1">{{ errors.email }}</div>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-3 col-form-label text-muted text-end-sm">Số điện thoại</label>
                            <div class="col-sm-9">
                                <input v-model="user.phone" type="tel" class="form-control"
                                    :placeholder="user.phone ? '' : 'Nhập số điện thoại của bạn'">
                                <div v-if="errors.phone" class="text-danger small mt-1">{{ errors.phone }}</div>
                            </div>
                        </div>

                        <div class="mb-3 row align-items-center">
                            <label class="col-sm-3 col-form-label text-muted text-end-sm">Giới tính</label>
                            <div class="col-sm-9 d-flex gap-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="male" value="male"
                                        v-model="user.gender">
                                    <label class="form-check-label" for="male">Nam</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="female"
                                        value="female" v-model="user.gender">
                                    <label class="form-check-label" for="female">Nữ</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="gender" id="other" value="orther"
                                        v-model="user.gender">
                                    <label class="form-check-label" for="other">Khác</label>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-sm-9 offset-sm-3">
                                <button type="submit" class="btn btn-brand px-4 py-2 rounded-3 shadow-sm">Lưu thay
                                    đổi</button>
                            </div>
                        </div>

                    </div>

                    <div class="col-md-4 d-flex flex-column align-items-center justify-content-center pt-4 pt-md-0">
                        <div class="avatar-upload mb-3 position-relative">
                            <img :src="user.avatarPreview || defaultAvatar" class="rounded-circle shadow-sm border p-1"
                                style="width: 150px; height: 150px; object-fit: cover;">
                        </div>

                        <label class="btn btn-outline-secondary btn-sm rounded-3 cursor-pointer">
                            <i class="bi bi-upload me-1"></i> Chọn ảnh
                            <input type="file" hidden @change="handleAvatarChange" accept="image/*">
                        </label>

                        <div class="text-muted small mt-3 text-center">
                            Dung lượng file tối đa 1 MB<br>
                            Định dạng: .JPEG, .PNG
                        </div>
                    </div>

                </div>
            </form>
        </div>
    </div>

</template>


<style scoped>
/* Màu chủ đạo */
:root {
    --brand-color: #3497e0;
}

.bg-light {
    background-color: #f4f6f8 !important;
}

/* Sidebar Menu Styles */
.list-group-item {
    border: none;
    color: #555;
    transition: all 0.2s;
}

.list-group-item:hover {
    color: #3497e0;
    background-color: #f8faff;
}

/* Item đang Active (đang ở trang Profile) */
.active-item {
    color: #3497e0;
    font-weight: 600;
    background-color: transparent;
    position: relative;
}

/* Thêm dấu gạch đứng nhỏ bên phải để báo hiệu active */
.active-item::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 20px;
    background-color: #3497e0;
    border-radius: 4px 0 0 4px;
}

/* Form Styles */
.text-end-sm {
    text-align: right;
}

.form-control:focus {
    border-color: #3497e0;
    box-shadow: 0 0 0 0.25rem rgba(52, 151, 224, 0.15);
}

.form-check-input:checked {
    background-color: #3497e0;
    border-color: #3497e0;
}

/* Nút Brand */
.btn-brand {
    background-color: #3497e0;
    border-color: #3497e0;
    color: white;
    transition: all 0.3s;
}

.btn-brand:hover {
    background-color: #287dbd;
    transform: translateY(-1px);
}

/* Responsive adjustment */
@media (max-width: 576px) {
    .text-end-sm {
        text-align: left !important;
        margin-bottom: 5px;
    }

    .border-end-md {
        border-right: none !important;
        border-bottom: 1px solid #eee;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
}
</style>
