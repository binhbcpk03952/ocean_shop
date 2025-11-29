<script setup>
import { ref, onMounted } from 'vue';
import api from '../../axios';
import ModalFormUser from '../../components/admin/ModalFormUser.vue';
const users = ref([])
const showModal = ref(false);
const modalMode = ref('create'); // 'create' hoặc 'edit'
const userData = ref({});

// lấy danh sách người dùng
const handleFetchUsers = async () => {
    try {
        const res = await api.get('/users')
        if (res.status === 200) {
            users.value = res.data
        }
    } catch (err) {
        console.log('Loi khi goi API: ', err);
    }
}
const handleEditUser = (user) => {
    userData.value = user;
    modalMode.value = 'edit';
    showModal.value = true;
}
const handleAddUser = () => {
    modalMode.value = 'create';
    userData.value = {};
    showModal.value = true;
}
const handleDeleteUser = async (id) => {
    const isConfirm = confirm('Bạn chắc chắn muốn xoá người dùng này?');

    if (isConfirm) {
        try {
            const res = await api.delete(`/users/${id}`);
            if (res.status === 200 && res.data.status) {
                alert('Xoá người dùng thành công');
                // Cập nhật lại danh sách sau khi xoá
                handleFetchUsers();
            } else if (res.status === 403) {
                alert(res.data.message || 'Xoá người dùng thất bại');
            }
        } catch (err) {
            console.log('Đã xảy ra lỗi khi xoá người dùng: ', err);
            alert(err.response.data.message || 'Đã có lỗi xảy ra khi xoá người dùng');
        }
    }
}




onMounted(() => {
    handleFetchUsers();
})

</script>
<template>
    <ModalFormUser :open-modal="showModal" :mode="modalMode" :user-data="userData" @close="showModal = false"
        @save="handleFetchUsers" />

    <h1 class="fs-2 mt-3">Quản lí người dùng</h1>

    <div class="fillter col-lg-3">
        <select class="form-select" width="200px">
            <option value="all">Mặc định</option>
            <option value="admin">Quản trị</option>
            <option value="user">Người dùng</option>
        </select>
    </div>
    <button class="btn btn-primary" @click="handleAddUser">Thêm người dùng</button>
    <table class="table table-striper table hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Tên người dùng</th>
                <th>Email</th>
                <th>Quyền</th>
                <th>Trạng thái</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(user, index) in users" :key="user.user_id">
                <td>{{ index + 1 }}</td>
                <td>{{ user.name }}</td>
                <td>{{ user.email }}</td>
                <td>
                    <small class="bg-info rounded-5 px-2 py-1 text-white">{{ user.role === "admin" ? 'Quản trị' : 'Người dùng' }}</small>

                </td>
                <td>{{ user.status }}</td>
                <td>
                    <button class="btn btn-outline-primary me-2" @click="handleEditUser(user)">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-outline-danger" @click="handleDeleteUser(user.user_id)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>


</template>
<style scoped></style>
