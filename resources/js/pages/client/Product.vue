<script setup>
import { reactive, onMounted, ref, toRaw } from 'vue';
import { useRouter } from 'vue-router';
</script>
<template>
    <div class="container">
        <h1  style="font-size: 1.5rem;" >Trang chủ / <span style="font-size: 1rem; font-weight: 400;">Thời trang</span></h1>
        <div class="filter-1">
            <button>Nam</button>
            <button>Nữ</button>
            <button>Trẻ em</button>
            <button>Set đồ mùa đông</button>
        </div>
        <div class="filter-2">
            <a class="nav-link" href="">Ưu đãi</a>
            <a class="nav-link" href="">Mới nhất</a>
            <button class="nav-link" href=""><i class="bi bi-funnel"></i> Lọc</button>
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
                        <button class ="btn-color"></button>
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
                        <button class ="btn-color"></button>
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
                        <button class ="btn-color"></button>
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
div.item{
    margin: 20px 50px 0px 0px;
}
div.row{
    margin-top: 50px;
    margin-bottom: 50px;
}

div.filter-1 button{
    width: 170px;
    padding: 10px;
    margin: 5px;
    border: 0.3px solid black;
}
div.filter-2 {
    margin: 20px 0px 0px 0px;
}
div.filter-2 a{
    margin: 10px;
}
div.filter-2 button{
    border: 1px solid black;
    padding: 5px 10px;
}
</style>