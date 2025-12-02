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
  <div class="container py-4">

    <!-- TIÊU ĐỀ + SEARCH -->
    <div class="d-flex flex-column align-items-center mb-4">
      <h1 class="fw-bold mb-3">Tin tức</h1>

      <div class="input-group w-50 shadow-sm rounded-pill">
        <input type="text" class="form-control border-0 rounded-start-pill py-2"
          placeholder="Tìm kiếm...">
        <button class="btn btn-light border-0 rounded-end-pill">
          <i class="bi bi-search"></i>
        </button>
      </div>
    </div>


    <!-- TIN MỚI NHẤT -->
    <div class="row mb-5" v-if="posts.length">
      <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
          <img
            class="w-100"
            style="max-height: 350px; object-fit: cover;"
            :src="'../../../../storage/' + posts[0].thumbnail_path"
            alt=""
          />
          <div class="card-body">
            <h3 class="fw-bold">{{ posts[0].title }}</h3>
            <p class="text-muted small">{{ posts[0].created_at }}</p>

            <div class="post-desc fs-6" v-html="posts[0].content"></div>

            <router-link :to="'/blog/' + posts[0].post_id" class="btn btn-primary mt-3">
              Đọc thêm
            </router-link>
          </div>
        </div>
      </div>
    </div>


    <!-- DANH SÁCH BÀI VIẾT -->
    <div class="row g-4">
      <div class="col-md-4" v-for="post in posts" :key="post.post_id">
        <router-link :to="'/blog/' + post.post_id" class="text-decoration-none text-dark">
          <div class="card border-0 shadow-sm rounded-4 overflow-hidden h-100 post-card">
            <img
              :src="'../../../../storage/' + post.thumbnail_path"
              class="card-img-top"
              style="height: 200px; object-fit: cover;"
            />

            <div class="card-body">
              <h5 class="fw-bold">{{ post.title }}</h5>
              <p class="post-desc mt-2" v-html="post.content"></p>
            </div>
          </div>
        </router-link>
      </div>
    </div>


    <!-- LOADING -->
    <div class="container text-center p-5" v-if="!posts.length">
      <div class="spinner-border text-primary" role="status"></div>
      <p class="mt-3">Đang tải bài viết...</p>
    </div>

  </div>
</template>

<style scoped>
.post-desc {
  overflow: hidden;
  text-overflow: ellipsis;
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  font-weight: 300;
  font-size: 15px;
}

.post-card:hover {
  transform: translateY(-5px);
  transition: 0.2s;
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.12) !important;
}

input:focus {
  box-shadow: none !important;
}

</style>
