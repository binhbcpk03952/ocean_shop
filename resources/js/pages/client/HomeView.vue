<script setup>
import SliderClient from '../../components/client/SliderClient.vue'
import ChatBoxAI from '../../components/client/ChatBoxAI.vue'

// products list
import { ref, watch, reactive, onMounted } from 'vue';
import api from '../../axios';
import BoxProduct from '../../components/client/BoxProduct.vue';
const products = ref([])

const handleFetchProducts = async () => {
    try {
        const response = await api.get('/products')
        if (response.status === 200) {
            products.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách sản phẩm:', error)
    }
}

onMounted(() => {
    handleFetchProducts()
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



</style>
