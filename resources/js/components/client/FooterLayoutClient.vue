<script setup>
</script>
<template>
    <div class="div py-5" style="background-color: #0A3D62; color: white;">
        <div class="container">
            <footer class="row">
                <div class="logo">
                    <img src="../../../../public/images/logo_ocean.png" alt="logo Ocean" width="180px">
                </div>
                <div class="col-md-4">
                    <div class="about-me">
                        <h3>Về Ocean</h3>
                        <p>
                            Chúng tôi luôn quý trọng và tiếp thu mọi ý kiến đóng góp từ khách hàng, nhằm không ngừng cải
                            thiện
                            và nâng tầm trải nghiệm dịch vụ cũng như chất lượng sản phẩm.
                        </p>
                    </div>
                </div>
                <div class="col-md-4">
                    <h3>Hỗ trợ khách hàng</h3>
                    <ul class="p-0">
                        <li class="nav-link">Chính sách đổi trả</li>
                        <li class="nav-link">Hướng dẫn mua hàng</li>
                        <li class="nav-link">Liên hệ</li>
                    </ul>
                </div>
                <div class="col-md-4">
                    <h3>Kết nối</h3>
                    <div class="icon">
                        <a href="#" class="me-3">
                            <i class="bi bi-facebook"></i>
                        </a>
                        <a href="#" class="me-3">
                            <i class="bi bi-instagram"></i>
                        </a>
                        <a href="#" class="me-2">
                            <i class="bi bi-tiktok"></i>
                        </a>
                        <a href="#" class="me-3">
                            Zalo
                        </a>
                    </div>
                    <div class="contact-me">
                        <form @submit.prevent="handleContactMe">
                            <div class="input_value mt-2 d-flex align-items-center ">
                                <input type="email" name="email" id="email" class="me-1 py-1 px-2 rounded-2"
                                    placeholder="Nhập email của bạn">
                                <button class="btn py-1 px-3 btn-outline-primary">Gửi</button>
                            </div>
                        </form>
                    </div>
                </div>
                <hr style="margin: 20px 0px;">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="info">
                        <div class="h7">© CÔNG TY CỔ PHẦN THỜI TRANG OCEAN</div>
                        <small>Bản quyền thuộc về của chúng tôi!!</small>
                    </div>
                    <div class="image_logo d-flex align-items-center">
                        <img src="../../../../public/images/logo_ocean.png" alt="logo Ocean" width="180px">
                        <img src="../../../../public/images/FPT_Polytechnic.png" alt="logo FPT_Polytechnic">
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>
<style scoped>
header {
    font-family: 'Times New Roman', Times, serif;
}

#email {
    border: 1px solid #111;
}

#email:focus {
    outline: none;
    border: 1px solid #3497E0;
}

h3 {
    font-size: 1.2rem;
    margin-top: 20px;
}

p {
    font-size: 0.9rem;
}

ul li {
    font-size: 0.9rem;
}

div.logo {
    padding: 0;
}

img {
    width: 150px;
}

div.icon {
    font-size: 1.4rem;
}

div.icon a {
    color: white;
    text-decoration: none;
    font-weight: 300;
}
div.icon a:hover {
    transition: 0.3s;
    opacity: 0.5;
}
</style>
