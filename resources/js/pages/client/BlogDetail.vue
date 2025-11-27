<script setup>
import { useRoute } from 'vue-router';
import api from '../../axios';
import { ref, onMounted, watch } from 'vue';

const route = useRoute()
const post = ref(null)

const handleFetchPost = async (postId) => {
    try {
        const response = await api.get(`/posts/${postId}`)
        if (response.status === 200) {
            post.value = response.data
            console.log(post.value);
        } else {
            console.log('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy bài viết:', error)
    }
}

watch(() => route.params.id, (newPost) => {
    handleFetchPost(newPost)
})

onMounted(() => {
    handleFetchPost(route.params.id)
})
</script>
<template>
    <div class="container mt-3">
        <div class="row">
            <div class="col-md-8" v-if="post">
                <h1 class="fw-bold fs-3 mb-3">{{ post.title }}</h1>
                <div class="image_main">
                    <img :src="'../../../../storage/' + post.thumbnail_path" alt="images" class="w-100">
                </div>
                <div class="post-content">
                    <div class="post-desc" v-html="post.content"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    Danh mục
                </div>
            </div>
        </div>
    </div>
</template>
<style scoped>
.post-desc :deep(img) {
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    /* Thêm bo tròn nhẹ cho hình ảnh */
    margin: 1rem 0;
    /* Thêm khoảng cách trên và dưới cho hình ảnh */
}
</style>
