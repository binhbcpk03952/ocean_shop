<script setup>
import { ref, onMounted } from 'vue';
import api from '../../axios';
import { Tab } from 'bootstrap';
const posts = ref([])
// lấy danh sách bài viết
const handleFetchPosts = async () => {
    try {
        const response = await api.get('/posts')
        if (response.status === 200) {
            posts.value = response.data
        } else {
            console.log('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách bài viết:', error)
    }
}
// xóa bài viết
const handleDeletePost = async (postId) => {
    console.log(postId);
    const isConfirm = confirm('Bạn có chắc chắn muốn xóa bài viết này?')
    if (!isConfirm) return;

    try {
        const response = await api.delete(`/posts/${postId}`)
        if (response.status === 200) {
            alert(response.data.message)
            handleFetchPosts()
        } else {
            alert(response.data.message)
        }
    } catch (error) {
        console.error('Lỗi khi xóa bài viết:', error)
    }

}
onMounted(() => {
    handleFetchPosts()
})
</script>
<template>
    <h1>Trang quản lí bài viết</h1>
    <RouterLink to="add_post">Thêm bài viết</RouterLink>

    <table class="table table-striped table-hover mt-4 ms-4">
        <thead>
            <tr>
                <th width="80px">STT</th>
                <th><i class="bi bi-image"></i></th>
                <th>Tiêu đề</th>
                <th>Thao tác</th>
            </tr>
        </thead>
        <tbody>
            <tr v-if="posts" v-for="(post, index) in posts" :key="post.id">
                <td>{{ index + 1 }}</td>
                <td><img :src="'../../../storage/' + post.thumbnail_path" alt="Thumbnail" width="100px" /></td>
                <td>{{ post.title }}</td>
                <td>
                    <button class="btn btn-outline-primary py-1 me-2">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                    <button class="btn btn-outline-danger py-1" @click="handleDeletePost(post.post_id)">
                        <i class="bi bi-trash"></i>
                    </button>
                </td>
            </tr>
        </tbody>
    </table>
</template>
