<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../axios';

const carts = ref([])
const handleFetchCarts = async () => {
    try {
        const response = await api.get('/carts')
        if (response.status === 200) {
            carts.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách sản phẩm:', error)
    }
}
onMounted(() => {
    handleFetchCarts()
})
</script>
<template>
    <div class="container">
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <div class="select-all">
                            <input type="checkbox" id="select_all">
                            <label for="select_all">Chọn tất cả</label>
                        </div>
                        <div class="cart-list">
                            <template v-for="cart in carts.cart_item" :key="cart.cart_id">
                                <div class="row w-100 mt-3 align-items-center">
                                    <div class="inp-checkbox col-md-1">
                                        <input type="checkbox">
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center col-md-11">
                                        <div class="info-product">
                                            <div class="d-flex justify-content-between ">
                                                <div class="image-prd card me-3">
                                                    <img :src="'../../../storage/' + cart.variant.image[0].image_url"
                                                        alt="name-product" width="100px">
                                                </div>
                                                <div class="info">
                                                    <div class="name fs-5">{{ cart.variant.product.name }}</div>
                                                    <div class="variant d-flex">
                                                        <small> Size, Màu:</small>
                                                        <div class="card rounded-5 p-2 d-flex align-items-center">
                                                            <button class="btn border-primary btn-color"
                                                                :style="{ backgroundColor: cart.variant.color }"></button>
                                                            <span>{{ cart.variant.size }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="quantity">
                                                        <small>Số lượng:</small>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="option">
                                            <button>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>

                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</template>

<style scoped>
.btn-color {
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: inline-block;
}
</style>
