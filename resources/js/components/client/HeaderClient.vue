<script setup>
import { ref, onMounted, onBeforeUnmount, inject, watch } from 'vue';
import { useRoute } from 'vue-router';
import api from '../../axios'; const authStore = inject('auth')
import CatgoriesListHeader from './CatgoriesListHeader.vue';
const auth = authStore.auth
const checkLogin = authStore.checkLogin




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
console.log(auth);


const handleLogout = async () => {
    try {
        await api.post('/logout');
        // auth provider
        auth.loggedIn = false
        auth.name = ''
        auth.role = ''
        auth.token = ''
        await checkLogin();

        console.log('Đăng xuất thành công!');

    } catch (error) {
        console.error('Lỗi đăng xuất:', error);
    }
};

const show = ref(false);
const showBox = () => {
    show.value = true;

}
const closeBox = () => {
    show.value = false;
}

const route = useRoute();
watch(() => route.fullPath, () => {
    show.value = false;
})

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
        <div class="text-end color-primary mt-1 mb-0" v-if="auth.loggedIn">
            <span class="bg-secondary text-white px-3 rounded-5">
                Xin chào {{ auth.user }}
            </span>

        </div>
        <header class="py-3 header-sticky">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <nav>
                        <ul class="d-flex nav-items">
                            <li style="cursor: pointer;">
                                <router-link class="nav-link" to="/products">
                                    Thời trang <i @click.stop.prevent="showBox" class="bi bi-chevron-down"></i>
                                </router-link>
                            </li>
                            <li>
                                <router-link class="nav-link">Ưu đãi</router-link>
                            </li>
                            <li><router-link class="nav-link">Đồng phục</router-link></li>
                            <li><router-link to="/store" class="nav-link">Cửa hàng</router-link></li>
                            <li><router-link to="/blog" class="nav-link">Tin tức</router-link></li>
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
                            <router-link to="/carts" class="me-2 position-relative">
                                <i class="bi bi-bag text-black fs-4"></i>
                                <div class="stock-cart">
                                    <small>5</small>
                                </div>
                            </router-link>

                            <div class="user-login">
                                <span class="logged-in" v-if="auth.loggedIn" @click="handleDropdown" ref="dropdownRef">
                                    <i class="bi bi-person fs-3"></i>

                                    <div class="drop-down py-1" v-show="dropdown" style="z-index: 1030;">
                                        <router-link to="/profile" class="nav-link p-1 ps-3">Thông tin cá
                                            nhân</router-link>
                                        <router-link to="/admin" v-if="auth.role === 'admin'"
                                            class="nav-link p-1 ps-3">Quản trị</router-link>
                                        <span @click="handleLogout"
                                            class="nav-link p-1 ps-3 w-100 text-danger text-left">Đăng xuất</span>
                                    </div>
                                </span>

                                <router-link to="/login" v-else>
                                    <i class="bi bi-person fs-3"></i>
                                </router-link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- ===== box header =====  -->
        <div class="box_header-click position-fixed start-0 top-0 w-100 h-100" v-show="show"
            style="background-color: rgb(0, 0, 0, 0.5); z-index: 1040; height: 90vh;">
            <div class="bg-light w-100">
                <div class="show-category container bg-light" :class="{ 'open': show }">
                    <div class="header-items py-3 d-flex justify-content-between align-items-center">
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
                            <router-link to="carts" class="me-2 position-relative text-black text-decoration-none">
                                <i class="bi bi-bag fs-4"></i>
                                <div class="stock-cart">
                                    <small>5</small>
                                </div>
                            </router-link>
                            <span><i class="bi bi-person fs-3"></i></span>
                        </div>
                    </div>
                    <hr class="m-0">
                    <div class="navigation d-flex justify-content-center align-items-center my-4">
                        <ul class="d-flex nav-items">
                            <li class="nav-link">
                                <span class="btn btn-color fs-5">
                                 🆕   BST THU ĐÔNG
                                </span>
                            </li>
                            <li class="nav-link">
                                <span class="btn btn-color fs-5">
                                    <i class="bi bi-shop-window"></i>
                                    CỬA HÀNG

                                </span>
                            </li>
                            <li class="nav-link">
                                <span class="btn btn-color fs-5">
                                    <i class="bi bi-postcard"></i>
                                    TIN TỨC

                                </span>
                            </li>
                            <li class="nav-link">
                                <span class="btn btn-color fs-5">
                                    <i class="bi bi-basket2 fs-5"></i>
                                    MỚI VỀ
                                </span>
                            </li>
                            <li class="nav-link">
                                <span class="btn btn-color fs-5">
                                    <i class="bi bi-tags"></i>
                                     ƯU ĐÃI
                                </span>
                            </li>
                        </ul>
                    </div>
                    <!-- catgory products -->
                    <CatgoriesListHeader />
                </div>
            </div>
            <div class="close-box text-center">
                <span @click="closeBox" class="bg-light btn bg-white rounded-5 mt-1" style="cursor: pointer;">
                    <i class="bi bi-x-lg"></i>
                    Đóng
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

.header-sticky {
    position: sticky;
    top: 0;
    z-index: 1030;
    background-color: white;
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
    background-color: rgba(210, 207, 207, 0.5);
}
.btn-color:hover {
    background-color: #3497E0;
    color: white;
}

.show-category {
    max-height: 0;
    overflow: hidden;
    transition: max-height 1200ms ease-in-out;
}

.show-category.open {
    max-height: 80vh;
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
