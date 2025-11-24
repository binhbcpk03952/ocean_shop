<script setup>
import { reactive, onMounted, ref, toRaw, computed } from 'vue';
import { useRouter } from 'vue-router';
// import call api
import api from '../axios';

// Hide API
const posts = ref([])
onMounted(async () => {
    try {
        const response = await api.get('/posts')
        if (response.status === 200) {
            posts.value = response.data
        } else {
            console.log('error')
        }
    } catch (error) {
        console.error('bug posts', error)
    }
})
</script>

<template>
    <div class="container">
        <h1 style="font-size: 1.5rem;">Trang chủ / <span style="font-size: 1rem; font-weight: 400;">Tin tức</span></h1>
        <center style="position: relative;">
            <input type="text" placeholder="Tìm kiếm bài viết..."
                style="width: 50%; padding: 13px; padding-left: 20px; margin: 20px 0; border: 1px solid #ccc; border-radius: 20px;">
            <i class="bi bi-search"></i>
        </center>
        <div class="row">
            <div class="col-md-6">
                <img src="../../../public/storage/images/blogs/macbookvawindows.png" alt="">
                <h1>YODY ra mắt Tạp chí "YODY in LIFE" - Ấn phẩm số 1</h1>
                <p>Mở đầu cho "YODY in LIFE", ấn phẩm số 1 mang chủ đề "Thu Đông 2025 - Dòng chảy kết nối".</p>
            </div>
            <div class="col-md-6">
                <div class="row">
                    <div class="col-md-5">
                        <img src="../../../public//storage/images/blogs/pin iphone.jpg" alt="">
                    </div>
                    <div class="col-md-5">
                        <h1>YODY ra mắt Tạp chí "YODY in LIFE" - Ấn phẩm số 1</h1>
                        <p>Mở đầu cho "YODY in LIFE", ấn phẩm số 1 mang chủ đề "Thu Đông 2025 - Dòng chảy kết nối".</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <img src="../../../public/storage/images/blogs/Thanh toan.jpg" alt="">
                    </div>
                    <div class="col-md-5">
                        <h1>YODY ra mắt Tạp chí "YODY in LIFE" - Ấn phẩm số 1</h1>
                        <p>Mở đầu cho "YODY in LIFE", ấn phẩm số 1 mang chủ đề "Thu Đông 2025 - Dòng chảy kết nối".</p>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <img src="../../../public/storage/images/blogs/tien-phat.jpg" alt="">
                    </div>
                    <div class="col-md-5">
                        <h1>YODY ra mắt Tạp chí "YODY in LIFE" - Ấn phẩm số 1</h1>
                        <p>Mở đầu cho "YODY in LIFE", ấn phẩm số 1 mang chủ đề "Thu Đông 2025 - Dòng chảy kết nối".</p>
                    </div>
                </div>
            </div>
        </div>

        <center>
            <h1>CÁC TRANG TIN TỨC KHÁC</h1>
        </center>

        <div class="row">
            <div class="col-md-4" v-if="posts" v-for="post in posts" :key="post.post_id">
                <router-link class="post-item" :to="'/blog/' + post.post_id">
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
        </div>
        <div class="row">
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
            <div class="col-md-4">
                <img src="../../../public//storage/images/blogs/macbookvawindows.png" alt="">
                <span>Date: 14/06/2025</span>
                <h1>Indie style là gì? Tìm hiểu phong cách thời trang đầy hoài niệm</h1>
            </div>
        </div>
        <center>
            <button>Xem thêm</button>
        </center>
    </div>
</template>
<style scoped>
center i {
    right: 27%;
    top: 35%;
    position: absolute;
    font-size: 1.2rem;
    color: #888;
}

div.row {
    margin-top: 30px;
}

div.col-md-6 h1 {
    font-size: 1.3rem;
    margin-top: 15px;
    font-weight: 600;
}

div.col-md-4 h1 {
    font-size: 1.3rem;
    margin-top: 15px;
    font-weight: 600;
}

div.col-md-6 p {
    font-size: 1rem;
    margin-top: 10px;
    color: #555;
}

div.col-md-6 img {
    width: 100%;
    border-radius: 10px;
}

div.col-md-4 img {
    width: 100%;
    height: 40%;
    border-radius: 10px;
}

div.col-md-4 {
    height: 050%;
    margin-bottom: 50px;
}

center h1 {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 40px 0 20px 0;
}

center button {
    width: 170px;
    padding: 10px;
    margin: 5px;
    border: 0.3px solid black;
    margin-bottom: 50px;
}
</style>