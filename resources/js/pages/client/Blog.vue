<script setup>
import { reactive, onMounted, ref, toRaw, computed } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../axios';

const posts = ref([])
onMounted(async () => {
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
})

// const newPost = computed(() => {
//     if (!posts.value || posts.value.length === 0) {
//         return null;
//     }
//     return posts.value[0]
// })
// console.log(newPost.value);



</script>
<template>
    <div class="container">
        <h1>Tin tức</h1>
        <div class="text-center">
            <div class="search">
                <input type="text" class="py-2 bg-white border-1 rounded-5 ps-2 ouline-none" placeholder="Tìm kiếm...">
                <button type="submit" class="py-1 bg-white border-0">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </div>
        <div class="row">
            <div class="col md-6">
                <div class="post-new">
                    <h2>Tin mới nhất</h2>
                    <div class="post-info">
                        <!-- <img src="" alt=""> -->
                    </div>
                </div>
            </div>
            <div class="colmd-6"></div>

        </div>
        <div class="row">
            <div class="col-md-4" v-if="posts" v-for="post in posts" :key="post.post_id">
                <router-link class="post-item text-decoration-none text-dark" :to="'/blog/' + post.post_id">
                    <div class="post-img">
                        <img :src="'../../../../storage/' + post.thumbnail_path" alt="images" class="w-100">
                    </div>
                    <div class="post-content">
                        <div class="post-title fw-bold">
                            {{ post.title }}
                        </div>
                        <div class="post-desc mt-2" v-html="post.content"></div>
                    </div>
                </router-link>
            </div>
            <div class="container text-center p-5" v-else>
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Đang tải bài viết...</p>
            </div>
        </div>
    </div>


</template>
<style scoped>
.post-desc {
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    /* Giới hạn hiển thị 3 dòng */
    -webkit-box-orient: vertical;
    font-weight: 300;
    font-size: 15px;
}

/* .post-desc  img {
    width: 100%;
    height: auto;
} */
</style>
