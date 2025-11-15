<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import api from '../../axios';
axios.defaults.withCredentials = true;
axios.defaults.baseURL = 'http://127.0.0.1:8000/';

const dropdownRef = ref(null);
const dropdown = ref(false);

const user = ref(null);

const loadUserData = () => {
    const userData = localStorage.getItem('user_data');
    if (userData) {
        user.value = JSON.parse(userData);
    }
};

loadUserData();



const handleDropdown = () => {
    dropdown.value = !dropdown.value;
}
const handleClickOutside = (event) => {
    if (dropdown.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdown.value = false;
    }
};

const handleLogout = async () => {
    try {
        // Gọi API Đăng xuất (Axios sẽ gửi token)
        await api.post('/logout');

        // 1. XÓA TOKEN VÀ THÔNG TIN NGƯỜI DÙNG TỪ LOCAL STORAGE
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');

        // 2. Chuyển hướng người dùng về trang Đăng nhập
        // router.push('/login');

        console.log('Đăng xuất thành công!');

    } catch (error) {
        console.error('Lỗi đăng xuất:', error);
        // Vẫn nên xóa token ngay cả khi API lỗi (để người dùng có thể thử đăng nhập lại)
        localStorage.removeItem('auth_token');
        localStorage.removeItem('user_data');
        // router.push('/login');
    }
};

const show = ref(false);
const showBox = () => {
    show.value = true;
}
const closeBox = () => {
    show.value = false;
}

//
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div class="container">


        <div class="text-end color-primary mt-1 mb-0" v-if="user">
            <span class="bg-secondary text-white px-3 rounded-5">
                Xin chào {{ user.name }}
            </span>

        </div>
        <header style="background-color: white;" class="py-3 position-sticky top-0">
            <div class="d-flex justify-content-between align-items-center">
                <nav>
                    <ul class="d-flex nav-items">
                        <li style="cursor: pointer;">
                            <span @click="showBox">
                                Thời trang <i class="bi bi-chevron-down"></i>
                            </span>
                        </li>
                        <li>
                            <span>Ưu đãi <i class="bi bi-chevron-down"></i></span>
                        </li>
                        <li><span>Đồng phục</span></li>
                        <li><span>Cửa hàng</span></li>
                        <li><span>Tin tức</span></li>
                    </ul>
                </nav>
                <div class="logo">
                    <router-link to="/">
                        <img src="../../../../public/images/logo_ocean.png" alt="Logo Ocean" width="140px">
                    </router-link>
                </div>
                <div class="end-items d-flex justify-content-between align-items-center">
                    <div class="search me-3 position-relative">
                        <input type="text" id="search" placeholder="Tìm kiếm...">
                        <button type="submit" class="position-absolute start-0 btn-search">
                            <i class="bi bi-search fs-5"></i>
                        </button>
                    </div>
                    <div class="user d-flex align-items-center">
                        <span class="me-2 position-relative">
                            <i class="bi bi-bag fs-4"></i>
                            <div class="stock-cart">
                                <small>5</small>
                            </div>
                        </span>
                        <div class="user-login">
                            <span class="logged-in" v-if="user" @click="handleDropdown" ref="dropdownRef">
                                <i class="bi bi-person fs-3"></i>
                                <div class="drop-down py-1" v-show="dropdown">
                                    <router-link to="/profile" class="nav-link p-1 ps-3">Thông tin cá nhân</router-link>
                                    <router-link to="/admin" v-if="user.role === 'admin'" class="nav-link p-1 ps-3">Quản
                                        trị</router-link>
                                    <span @click="handleLogout"
                                        class="nav-link p-1 ps-3 w-100 text-danger text-left">Đăng
                                        xuất</span>
                                </div>
                            </span>
                            <router-link to="/login" v-else><i class="bi bi-person fs-3"></i></router-link>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <div class="box_header-click position-fixed start-0 top-0 w-100 h-100" v-if="show"
            style="background-color: rgb(0, 0, 0, 0.06);">
            <div class="bg-light w-100">
                <div class="show-category container bg-light" :class="{ 'open': show }">
                    <div class="header-items d-flex justify-content-between align-items-center">
                        <div class="logo-items">
                            <router-link to="/">
                                <img src="../../../../public/images/logo_ocean.png" alt="logo ocean" width="140px">
                            </router-link>
                        </div>
                        <div class="search-items">
                            <input type="text" id="search-item" placeholder="Tìm kiếm...">
                            <i class="bi bi-search fs-5 search-icon"></i>
                        </div>
                        <div class="user-items d-flex align-items-center">
                            <span class="me-2 position-relative">
                                <i class="bi bi-bag fs-4"></i>
                                <div class="stock-cart">
                                    <small>5</small>
                                </div>
                            </span>
                            <span><i class="bi bi-person fs-3"></i></span>
                        </div>
                    </div>
                    <div class="navigation d-flex justify-content-center align-items-center mb-3">
                        <ul class="d-flex nav-items">
                            <li class="nav-link"><span class="btn btn-color fs-5">BST THU ĐÔNG</span></li>
                            <li class="nav-link"><span class="btn btn-color fs-5"><i class="bi bi-shop-window"></i> CỬA
                                    HÀNG</span></li>
                            <li class="nav-link"><span class="btn btn-color fs-5"><i class="bi bi-postcard"></i> TIN
                                    TỨC</span>
                            </li>
                            <li class="nav-link"><span class="btn btn-color fs-5">MỚI VỀ</span></li>
                            <li class="nav-link"><span class="btn btn-color fs-5"><i class="bi bi-tags"></i> ƯU
                                    ĐÃI</span>
                            </li>
                        </ul>
                    </div>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="category_list">
                                <h2>Nam</h2>
                                <ul>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                    <li>Quần nam</li>
                                    <li>Giày nam</li>
                                    <li>Phụ kiện nam</li>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="category_list">
                                <h2>Nam</h2>
                                <ul>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                    <li>Quần nam</li>
                                    <li>Giày nam</li>
                                    <li>Phụ kiện nam</li>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="category_list">
                                <h2>Nam</h2>
                                <ul>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                    <li>Quần nam</li>
                                    <li>Giày nam</li>
                                    <li>Phụ kiện nam</li>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="category_list">
                                <h2>Nam</h2>
                                <ul>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                    <li>Quần nam</li>
                                    <li>Giày nam</li>
                                    <li>Phụ kiện nam</li>
                                    <li>Áo khoác nam</li>
                                    <li>Áo sơ mi nam</li>
                                    <li>Áo thun nam</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="close-box text-center">
                <span @click="closeBox" class="bg-light" style="cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                    Dong
                </span>
            </div>
        </div>
    </div>
</template>

<style scoped>
ul.nav-items {
    list-style: none;
    margin: 0;
    padding: 0;
}

ul.nav-items li {
    margin-right: 20px;
    font-weight: 500;
}

#search {
    width: 230px;
    height: 33px;
    padding: 0 5px 0 30px;
    border-radius: 23px;
    border: 1px solid black;
}

#search:focus {
    outline: none;
}

.btn-search {
    background-color: transparent;
    border: none;
    cursor: pointer
}

.stock-cart {
    position: absolute;
    top: -2px;
    right: -9px;
    font-size: 12px;
    width: 15px;
    height: 15px;
    border-radius: 50%;
    background-color: #3497E0;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 2px;
}

.logo {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

header {
    position: relative;
}

#search-item {
    width: 450px;
    height: 38px;
    padding: 0 5px 0 30px;
    border-radius: 23px;
    border: 1px solid rgba(0, 0, 0, 0.201);
}

#search-item::placeholder {
    color: rgba(0, 0, 0, 0.479);
    font-size: 14px;
}

#search-item:focus {
    outline: none;
    border: 1px solid #3497E0;

}

.search-items {
    position: relative;
}

.search-icon {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    left: 10px;
}

.btn-color {
    background-color: rgba(0, 0, 0, 0.063);
}

.show-category {
    max-height: 0;
    overflow: hidden;
    transition: max-height 1200ms ease-in-out;
}

.show-category.open {
    max-height: 100%;
}

.logged-in {
    cursor: pointer;
    position: relative;
}

.drop-down {
    position: absolute;
    top: 30px;
    right: 0;
    background-color: white;
    border: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-direction: column;
    min-width: 180px;
    z-index: 1000;
    width: 100%;
}

.drop-down a:hover,
.drop-down span:hover {
    color: #3497E0;
    background-color: rgb(0, 0, 1, 0.1);
    border-radius: 5px;
}
</style>
