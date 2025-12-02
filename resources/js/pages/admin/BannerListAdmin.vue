<script setup>
import api from '../../axios';
import { ref, onMounted, } from 'vue';
const banners = ref([]);

const handleFetchBanners = async () => {
    try {
        const response = await api.get('/banners')
        if (response.status === 200) {
            banners.value = response.data
        }
    } catch (err) {
        console.log('Đã xảy ra lỗi khi goi API: ', err);
    }
}
// xoá banner

const handleDeleteBanner = async (id) => {
    const isConfirm = confirm('Bạn chắc chắc muốn xoá banner này')

    if (isConfirm) {
        try {
            const res = await api.delete(`/banners/${id}`)
            if (res.status === 200 && res.data.status) {
                alert('Xoá banner thành công')
                // Cập nhật lại danh sách sau khi xoá
                handleFetchBanners()
            }
        } catch (err) {
            console.log('Đã xảy ra lỗi khi xoá banner: ', err);
        }
    }
}



onMounted(() => {
    handleFetchBanners()
})


</script>

<template>
    <h2>Quản lí Banner</h2>
    <div class="text-end">
        <router-link to="/admin/add_banner" class="btn btn-primary">Thêm Banner</router-link>
    </div>

    <table class="table table-hover">
        <thead>
            <tr>
                <th>#</th>
                <th>Hình ảnh</th>
                <th>Đường dẫn</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="banners" v-for="(banner, index) in banners" :key="banner.banner_id">
                <td>{{ index + 1 }}</td>
                <td width="300px">
                    <img :src="'../../../../storage/' + banner.images" alt="banner Ocean_Shop" class="w-100">
                </td>
                <td>
                    <span>{{ banner.link }}</span>
                </td>
                <td>
                <td>
                    <button class="btn btn-outline-primary py-1 me-2">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-outline-danger py-1" @click="handleDeleteBanner(banner.banner_id)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
                </td>
            </tr>
        </tbody>
    </table>
</template>
