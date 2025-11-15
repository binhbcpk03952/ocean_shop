<script setup>
import SliderClient from '../components/client/SliderClient.vue';
</script>
<template>
    <SliderClient/>
    <div class="product-list mb-5">
        <div class="text-center mt-5">
            <h2>ÁO KHOÁC GIÓ ĐA NĂNG</h2>
            <p>Trượt nước - Cản gió - Giữ ấm - Phù hợp với thời tiết!!</p>
        </div>
        <div class="row">
            <div class="col-md-3">
                <div class="product-item">
                    <div class="product-img">
                        <img src="../../../../public/images/product_demo.webp" alt="product_demo" class="w-100">
                    </div>
                    <div class="product-info">
                        <div class="price text-danger fw-medium fs-5 mt-3">
                            449.100đ
                        </div>
                        <div class="product-name">
                            Áo Khoác Gió Nữ Gia Đinh 3C Promax
                        </div>
                    </div>
                    <div class="variant-colors d-flex  align-items-center">
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color active"></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="product-item">
                    <div class="product-img">
                        <img src="../../../../public/images/product_demo.webp" alt="product_demo" class="w-100">
                    </div>
                    <div class="product-info">
                        <div class="price text-danger fw-medium fs-5 mt-3">
                            449.100đ
                        </div>
                        <div class="product-name">
                            Áo Khoác Gió Nữ Gia Đinh 3C Promax
                        </div>
                    </div>
                    <div class="variant-colors d-flex  align-items-center">
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color active"></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="product-item">
                    <div class="product-img">
                        <img src="../../../../public/images/product_demo.webp" alt="product_demo" class="w-100">
                    </div>
                    <div class="product-info">
                        <div class="price text-danger fw-medium fs-5 mt-3">
                            449.100đ
                        </div>
                        <div class="product-name">
                            Áo Khoác Gió Nữ Gia Đinh 3C Promax
                        </div>
                    </div>
                    <div class="variant-colors d-flex  align-items-center">
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color active"></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="product-item">
                    <div class="product-img">
                        <img src="../../../../public/images/product_demo.webp" alt="product_demo" class="w-100">
                    </div>
                    <div class="product-info">
                        <div class="price text-danger fw-medium fs-5 mt-3">
                            449.100đ
                        </div>
                        <div class="product-name">
                            Áo Khoác Gió Nữ Gia Đinh 3C Promax
                        </div>
                    </div>
                    <div class="variant-colors d-flex  align-items-center">
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color"></button>
                        <button class="btn-color active"></button>
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
    border: 1px solid rgba(0, 0, 0, 0.134);
    margin-right: 5px
}

.btn-color:nth-child(2) {
    background-color: #143c39;
}

.btn-color:nth-child(3) {
    background-color: #EC873D;
}

.btn-color:nth-child(4) {
    background-color: #c69a53;
}

.btn-color.active {
    border: 2px solid #3497E0;
    ;
}
</style>
