<script setup>
import SliderClient from '../../components/client/SliderClient.vue'
import ChatBoxAI from '../../components/client/ChatBoxAI.vue'

// products list
import { ref, watch, reactive, onMounted } from 'vue';
import api from '../../axios';
import BoxProduct from '../../components/client/BoxProduct.vue';
const products = ref([]);
const posts = ref([])

//  Lấy danh sách product
const handleFetchProducts = async () => {
    try {
        const response = await api.get('/products')
        if (response.status === 200) {
            products.value = response.data.data.data || response.data || [];
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách sản phẩm:', error)
    }
};

// Lấy danh sách post
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
};

onMounted(() => {
    handleFetchProducts();
    handleFetchPosts();
})

</script>

<template>
  <SliderClient/>

  <div class="container product-list mb-5">

    <div class="text-center mt-5 content">
      <h2>ÁO KHOÁC GIÓ ĐA NĂNG</h2>
      <p>Trượt nước - Cản gió - Giữ ấm - Phù hợp với thời tiết!!</p>
    </div>

    <div class="row">
      <BoxProduct
      class="col-6 col-md-4 col-lg-3 item"
                v-for="product in products"
                :key="product.product_id"
                :product="product"
            />
    </div>

        <div class="text-center mt-5 content">
      <h2>BLOG FOR OCEAN</h2>
      <p>Đây là trang tin tức của chúng tôi!!</p>
    </div>
    <div class="row mt-5">
                  <div class="col-md-3" v-if="posts" v-for="post in posts" :key="post.post_id">
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

  <!-- 🚀 Quan trọng nhất: ChatBox nằm đây -->
  <ChatBoxAI/>
</template>


<style scoped>
.btn-color {
  width: 20px;
  height: 20px;
  border-radius: 50%;
  border: 1px solid rgba(0,0,0,0.134);
  margin-right: 5px
}

/* .btn-color:nth-child(2) { background-color: #143c39; }
.btn-color:nth-child(3) { background-color: #EC873D; }
.btn-color:nth-child(4) { background-color: #c69a53; } */

.btn-color.active {
  border: 2px solid #3497e0;
}

/* css posts */
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



</style>
