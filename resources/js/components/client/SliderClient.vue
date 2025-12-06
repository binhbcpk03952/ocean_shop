<script setup>
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Pagination, Scrollbar, A11y } from 'swiper/modules';
import { ref, onMounted } from 'vue';
import api from '../../axios';
import 'swiper/css/navigation';
import 'swiper/css';
import 'swiper/css/autoplay';
import { Autoplay } from 'swiper/modules';
const modules = [Navigation, Pagination, Scrollbar, A11y, Autoplay]
const banners = ref([]);
onMounted(async () => {
    try {
        const response = await api.get('/banners');
        if (response.status === 200) {
            banners.value = response.data
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách banner:', error);
    }
});

// const banners = [
//     '/src/assets/img/banner3.jpg',
//     '/src/assets/img/banner2.jpg',
// ];
</script>


<template>
    <div class="slider" style="margin-bottom:  50px; z-index: 1;">
        <Swiper
            :modules="modules"
            :loop="true"
            :autoplay="{ delay: 5000 }"
            style="width: 100%; height: auto;"
            :slides-per-view="1"
            :space-between="50"
            :navigation="true"  :pagination="{ clickable: true }"
            :scrollbar="{ draggable: true }"
        >
            <SwiperSlide v-for="(banner, idx) in banners" :key="banner.banner_id">
                <RouterLink v-if="banner.link" :to="'/' + banner.link">
                    <img :src="'../../../../storage/' + banner.images" :alt="`Slide ${idx + 1}`" class="w-100" />
                </RouterLink>
                <img v-else :src="'../../../../storage/' + banner.images" :alt="`Slide ${idx + 1}`" class="w-100" />
            </SwiperSlide>
        </Swiper>
    </div>
    <div class="slider container">
        <img src="../../../../public/images/slider_bottom.png" alt="slider_2" class="w-100">
    </div>
</template>

<style scoped>
:deep(.swiper-button-prev) {
    left: 60px; 
}

:deep(.swiper-button-next) {
    right: 60px; 
}
:deep(.swiper-button-prev::after), 
:deep(.swiper-button-next::after) {
    font-size: 20px; 
}
</style>
