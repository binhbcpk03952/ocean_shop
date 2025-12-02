<script setup>
import { reactive, onMounted, watch } from 'vue';
import api from '../../axios';
const props = defineProps({
    openModal: Boolean,
    mode: {
        type: String,
        default: 'create' // 'create' hoặc 'edit'
    },
    userData: {
        type: Object,
        default: () => ({})
    }
})

// Khi chế độ là 'edit', điền dữ liệu người dùng vào form

const emit = defineEmits(['close', 'save'])
// form data
const user = reactive({
    user_id: null,
    name: '',
    email: '',
    password: '',
    role: 'user',
    status: 'active',
})

const resetForm = () => {
    Object.assign(user, {
        name: '',
        email: '',
        password: '',
        role: 'user',
        status: 'active',
    });
}
// thông báo lỗi
const errorMessages = reactive({
    name: '',
    email: '',
    password: '',
    role: '',
})

const validateForm = () => {
    let isValid = true;
    // Reset lỗi
    errorMessages.name = '';
    errorMessages.email = '';
    errorMessages.password = '';
    errorMessages.role = '';

    if (!user.name) {
        errorMessages.name = 'Tên người dùng là bắt buộc.';
        isValid = false;
    }
    if (!user.email) {
        errorMessages.email = 'Email là bắt buộc.';
        isValid = false;
    }
    if (props.mode === 'create' && !user.password) {
        errorMessages.password = 'Mật khẩu là bắt buộc.';
        isValid = false;
    }
    return isValid;
}

const closeModal = () => {
    // validateForm(); // reset lỗi khi đóng
    if (props.mode === 'create') {
        resetForm();
    }
    emit('close')
}
watch([() => props.openModal, () => props.mode], ([isOpen, mode]) => {
    if (isOpen) {
        if (mode === 'create') {
            resetForm();
        }
        // mode edit sẽ watch từ userData

    }
});

watch(() => props.userData, (newData) => {
    if (props.mode === 'edit' && newData && Object.keys(newData).length > 0) {
        user.user_id = newData.user_id || null;
        user.name = newData.name || '';
        user.email = newData.email || '';
        user.role = newData.role || 'user';
        user.status = newData.status || 'active';
        user.password = '';
        user.password_confirmation = '';
    }
}, { immediate: true, deep: true });
const handleUser = async () => {
    if (!validateForm()) {
        return;
    }

    try {
        if (props.mode === 'create') {
            // Tạo người dùng mới
            const res = await api.post('/register', {
                name: user.name,
                email: user.email,
                password: user.password,
                role: user.role,
            });
            if (res.status === 201) {
                alert('Thêm người dùng thành công!');
                emit('save');
                closeModal();
            }
        } else if (props.mode === 'edit') {
            // Cập nhật người dùng
            const res = await api.put(`/users/${props.userData.user_id}`, {
                name: user.name,
                email: user.email,
                role: user.role,
            });
            if (res.status === 200) {
                alert('Cập nhật người dùng thành công!');
                emit('save');
                closeModal();
            }
        }
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            const errors = error.response.data.errors;
            for (const key in errors) {
                if (errorMessages.hasOwnProperty(key)) {
                    errorMessages[key] = errors[key][0];
                }
            }
        } else {
            alert('Đã có lỗi không mong muốn xảy ra. Vui lòng thử lại.');
            console.error(error);
        }
    }
}

</script>

<template>
    <div v-if="openModal" class="modal fade show" tabindex="-1"
        style="display: block; background-color: rgba(0,0,0,0.5)" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <!-- Header -->
                <div class="modal-header bg-gradient border-0 py-4">
                    <div>
                        <div class="modal-title fw-bold color-main mb-0 fs-5">
                            <i :class="props.mode === 'create' ? 'bi bi-person-plus' : 'bi bi-pencil-square'"
                                class="fs-4"></i>
                            {{ props.mode === 'create' ? 'Thêm người dùng mới' : 'Cập nhật thông tin người dùng' }}
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-black" @click="closeModal"></button>
                </div>

                <!-- Body -->
                <div class="modal-body p-4">
                    <form @submit.prevent="handleUser()">
                        <!-- Name Field -->
                        <div class="mb-4">
                            <label for="name" class="form-label fw-semibold text-dark">
                                <i class="bi bi-person fs-5"></i> Tên người dùng
                            </label>
                            <input type="text" class="form-control form-control-lg"
                                :class="{ 'is-invalid': errorMessages.name }" id="name" v-model="user.name"
                                placeholder="Nhập tên người dùng" />
                            <small v-if="errorMessages.name" class="d-block text-danger mt-2">
                                <i class="bi bi-exclamation-circle"></i> {{ errorMessages.name }}
                            </small>
                        </div>

                        <!-- Email Field -->
                        <div class="mb-4">
                            <label for="email" class="form-label fw-semibold text-dark">
                                <i class="bi bi-envelope fs-5"></i> Email
                            </label>
                            <input type="email" class="form-control form-control-lg"
                                :class="{ 'is-invalid': errorMessages.email }" id="email" v-model="user.email"
                                placeholder="Nhập địa chỉ email" />
                            <small v-if="errorMessages.email" class="d-block text-danger mt-2">
                                <i class="bi bi-exclamation-circle"></i> {{ errorMessages.email }}
                            </small>
                        </div>

                        <!-- Password Field -->
                        <div v-if="props.mode === 'create'" class="mb-4">
                            <label for="password" class="form-label fw-semibold text-dark">
                                <i class="bi bi-lock fs-5"></i> Mật khẩu
                            </label>
                            <input type="password" class="form-control form-control-lg"
                                :class="{ 'is-invalid': errorMessages.password }" id="password" v-model="user.password"
                                placeholder="Nhập mật khẩu" />
                            <small v-if="errorMessages.password" class="d-block text-danger mt-2">
                                <i class="bi bi-exclamation-circle"></i> {{ errorMessages.password }}
                            </small>
                        </div>

                        <!-- Role Field -->
                        <div class="mb-4">
                            <label for="role" class="form-label fw-semibold text-dark">
                                <i class="bi bi-shield-check fs-5"></i> Quyền
                            </label>
                            <select class="form-select form-select-lg" id="role" v-model="user.role">
                                <option value="user">👤 Người dùng</option>
                                <option value="admin">👑 Quản trị viên</option>
                            </select>
                        </div>
                        <div class="modal-footer bg-light border-top px-0">
                            <button type="button" class="btn btn-secondary" @click="closeModal">
                                <i class="bi bi-x-circle"></i> Đóng
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i :class="props.mode === 'create' ? 'bi bi-plus-circle' : 'bi bi-check-circle'"></i>
                                {{ props.mode === 'create' ? 'Thêm người dùng' : 'Cập nhật' }}
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Footer -->
            </div>
        </div>
    </div>
</template>

<style scoped>
:root {
    --color-main: #3497E0;
}

.color-main {
    color: var(--color-main);
}

.bg-gradient {
    background: linear-gradient(135deg, #3497E0 0%, #2a7bc4 100%);
}

.modal-content {
    border-radius: 12px;
    overflow: hidden;
}

.form-control,
.form-select {
    border-radius: 8px;
    border: 1px solid #e9ecef;
    transition: all 0.3s ease;
}

.form-control:focus,
.form-select:focus {
    border-color: #3497E0;
    box-shadow: 0 0 0 0.2rem rgba(52, 151, 224, 0.25);
}

.form-control.is-invalid,
.form-select.is-invalid {
    border-color: #dc3545;
}

.btn-lg {
    padding: 0.75rem 1.5rem;
    font-weight: 500;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background-color: #3497E0;
    border-color: #3497E0;
}

.btn-primary:hover {
    background-color: #2a7bc4;
    border-color: #2a7bc4;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(52, 151, 224, 0.3);
}

.btn-secondary:hover {
    transform: translateY(-2px);
}

.modal-header {
    padding: 1.5rem;
}

.modal-title {
    font-size: 1.25rem;
}

.form-label {
    margin-bottom: 0.75rem;
    font-size: 0.95rem;
}

.form-label i {
    margin-right: 0.5rem;
    color: #3497E0;
}
</style>
