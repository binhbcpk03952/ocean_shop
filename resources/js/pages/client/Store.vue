<script setup>
import { reactive, onMounted, ref, toRaw } from 'vue';
import { useRouter } from 'vue-router';</script>
<template>

    <body>
        <div class="imagebanner">
            <img src="../../../../public/images/BannerAboutOcean.png" alt="banner-about-ocean">
        </div>
        <div class="container">
            <div class="title">
                <h1>GIỚI THIỆU</h1>
                <hr style="width: 8%;">
            </div>
            <div class="content">
                <p>Chào mừng bạn đến với OCEAN – địa chỉ đáng tin cậy chuyên cung cấp các thời trang tại Đắk Lắk. Với
                    nhiều
                    năm kinh nghiệm, chúng tôi tự hào là người bạn đồng hành, giúp khách hàng lựa
                    chọn những thời trang phù hợp nhất với nhu-cầu và ngân sách.</p>
            </div>
            <div class="title">
                <h1>NGÔN NGỮ ĐỂ SỬ DỤNG TRONG DỰ ÁN</h1>
                <hr style="width: 30%;">
            </div>
            <div class="row">
                <div class="col-md-2">
                    <img src="../../../../public/images/Vue.png" alt="">
                    <h3>Vue</h3>
                </div>
                <div class="col-md-2">
                    <img src="../../../../public/images/html-logo.png" alt="">
                    <h3>HTML</h3>
                </div>
                <div class="col-md-2">
                    <img src="../../../../public/images/css-CfW5UZaa.svg" alt="">
                    <h3>CSS</h3>
                </div>
                <div class="col-md-2">
                    <img src="../../../../public/images/javascript-logo-javascript-icon-transparent-free-png.png"
                        alt="">
                    <h3>Javascript</h3>
                </div>
                <div class="col-md-2">
                    <img src="../../../../public/images/laravel.png" alt="">
                    <h3>Laravel</h3>
                </div>
                <div class="col-md-2">
                    <img src="../../../../public/images/PHP-logo.svg" alt="">
                    <h3>PHP</h3>
                </div>
            </div>
            <div class="title">
                <h1>CÁC THÀNH VIÊN CODE DỰ ÁN</h1>
                <hr style="width: 30%;">
            </div>
            <div class="row">
                <div class="col-md-3">
                    <img src="../../../../public/images/nv1.jpg" alt="">
                    <h3>Thành Bùi</h3>
                    <ul>
                        <li><a href=""><i class="bi bi-facebook"></i></a></li>
                        <li><a href=""><i class="bi bi-instagram"></i></a></li>
                        <li><a href=""><i class="bi bi-telegram"></i></a></li>
                        <li><a href=""><i class="bi bi-discord"></i></a></li>
                        <li><a href=""><i class="bi bi-github"></i></a></li>
                    </ul>
                    <p>Với hơn 10 năm kinh nghiệm trong ngành bán lẻ thiết bị di động, anh Bống là người đặt nền móng cho triết lý “thời trang” của OCEAN.</p>
                </div>
                <div class="col-md-3">
                    <img src="../../../../public/images/nv2.jpg" alt="">
                    <h3>Chí Bình</h3>
                    <ul>
                        <li><a href=""><i class="bi bi-facebook"></i></a></li>
                        <li><a href=""><i class="bi bi-instagram"></i></a></li>
                        <li><a href=""><i class="bi bi-telegram"></i></a></li>
                        <li><a href=""><i class="bi bi-discord"></i></a></li>
                        <li><a href=""><i class="bi bi-github"></i></a></li>
                    </ul>
                    <p>Với hơn 10 năm kinh nghiệm trong ngành bán lẻ thiết bị di động, anh Bống là người đặt nền móng cho triết lý “thời trang” của OCEAN.</p>
                </div>
                <div class="col-md-3">
                    <img src="../../../../public/images/nv3.jpg" alt="">
                    <h3>Đại Dương</h3>
                    <ul>
                        <li><a href=""><i class="bi bi-facebook"></i></a></li>
                        <li><a href=""><i class="bi bi-instagram"></i></a></li>
                        <li><a href=""><i class="bi bi-telegram"></i></a></li>
                        <li><a href=""><i class="bi bi-discord"></i></a></li>
                        <li><a href=""><i class="bi bi-github"></i></a></li>
                    </ul>
                    <p>Với hơn 10 năm kinh nghiệm trong ngành bán lẻ thiết bị di động, anh Bống là người đặt nền móng cho triết lý “thời trang” của OCEAN.</p>
                </div>
                <div class="col-md-3">
                    <img src="../../../../public/images/nv4.jpg" alt="">
                    <h3>Duy Tuyên</h3>
                    <ul>
                        <li><a href=""><i class="bi bi-facebook"></i></a></li>
                        <li><a href=""><i class="bi bi-instagram"></i></a></li>
                        <li><a href=""><i class="bi bi-telegram"></i></a></li>
                        <li><a href=""><i class="bi bi-discord"></i></a></li>
                        <li><a href=""><i class="bi bi-github"></i></a></li>
                    </ul>
                    <p>Với hơn 10 năm kinh nghiệm trong ngành bán lẻ thiết bị di động, anh Bống là người đặt nền móng cho triết lý “thời trang” của OCEAN.</p>
                </div>
            </div>
        </div>
    </body>
</template>
<style scoped>
body {
    font-family: 'Manrope';
}

div.title {
    margin: 45px;
}

div.title hr {
    margin: 0 auto;
    border: 2px solid black;
}

div.imagebanner img {
    width: 100%;
}

h1 {
    text-align: center;
    font-size: 1.8rem;
    font-weight: 1000;
}

div.content {
    width: 75%;
    margin: 0 auto;
    text-align: center;

}

div.row>div.col-md-2 {
    /* Thiết lập chiều cao cố định cho container để đảm bảo sự đồng đều */
    height: 200px;
    /* Điều chỉnh giá trị này (e.g., 200px) cho phù hợp */
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    /* Căn chỉnh nội dung từ trên xuống */
    align-items: center;
    /* Căn giữa nội dung theo chiều ngang */
    text-align: center;
    /* Đảm bảo h2 được căn giữa */
}

div.col-md-2 h3 {
    text-align: center;
    margin: 10px;
    font-weight: bold;
    font-size: 1.3rem;
}

div.col-md-2 img {
    /* Đặt một chiều cao tối đa cố định cho hình ảnh */
    max-height: 120px;
    /* Điều chỉnh giá trị này (e.g., 100px) */
    width: auto;
    /* Giữ tỷ lệ khung hình */
    object-fit: contain;
}

div.col-md-3 {
    display: flex;
    /* Bật Flexbox */
    flex-direction: column;
    /* Xếp chồng nội dung (ảnh rồi đến tên) */
    align-items: center;
    /* Căn giữa nội dung theo chiều ngang */
    text-align: center;
    /* Căn giữa văn bản h3 */
    /* Thêm khoảng cách nếu cần */
    padding: 15px 0;
}

div.col-md-3 img {
    border-radius: 50%;
    max-width: 250px;
}

div.col-md-3 h3 {
    text-align: center;
    margin: 10px;
    font-weight: bold;
    font-size: 1.3rem;
}

div.col-md-3 ul li {
    list-style: none;
    display: inline-block;
    margin-right: 18px;
}

div.col-md-3 ul li a {
    color: rgba(0, 0, 0, 0.63);
    font-size: 1.5rem;
}
div.col-md-3 p {
    font-size: 1rem;
}

div.col-md-3 ul {
    padding: 0;
}
</style>