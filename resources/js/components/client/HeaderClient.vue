<script setup>
import { ref, onMounted, onBeforeUnmount, inject, watch, nextTick, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../../axios';
import CatgoriesListHeader from './CatgoriesListHeader.vue';
import { emitter } from '../../stores/eventBus';

const authStore = inject('auth');
const auth = authStore.auth;
const checkLogin = authStore.checkLogin;

const router = useRouter();
const route = useRoute();

/* ---------------- CART ---------------- */
const stockInCart = ref(0);

const handleFetchStockInCart = async () => {
    try {
        const res = await api.get('/carts');
        if (res.status === 200) {
            stockInCart.value = res.data.cart_item?.length || 0;
        }
    } catch (error) {
        console.error("Lỗi lấy giỏ hàng:", error);
    }
};

/* ---------------- DROPDOWN USER ---------------- */
const dropdownRef = ref(null);
const dropdown = ref(false);

const handleDropdown = () => {
    dropdown.value = !dropdown.value;
};

const handleClickOutside = (event) => {
    if (dropdown.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdown.value = false;
    }
};

/* ---------------- LOGOUT ---------------- */
const handleLogout = async () => {
    try {
        await api.post('/logout');
        auth.loggedIn = false;
        auth.name = '';
        auth.role = '';
        auth.token = '';
        await checkLogin();
        router.push('/');
    } catch (error) {
        console.error("Lỗi đăng xuất:", error);
    }
};

/* ---------------- SEARCH ---------------- */
const products = ref([]);
const keyword = ref('');
const show = ref(false);
const searchInputRef = ref(null);

// Lấy danh sách sản phẩm 1 lần khi mount để filter client-side (cho nhanh với số lượng ít)
const fetchProducts = async () => {
    try {
        const res = await api.get('/products', {
            params: { limit: 100 }
        });

        const rawData = res.data.data.data || res.data || []; // Tùy cấu trúc trả về thực tế

        // MAP DỮ LIỆU: Biến đổi JSON server thành format Client cần
        products.value = rawData.map(product => {
            // 1. Xử lý ảnh: Lấy ảnh is_main = 1, nếu không có thì lấy ảnh đầu tiên
            const mainImageObj = product.image?.find(img => img.is_main === 1) || product.image?.[0];
            // Lưu ý: Cần thêm base URL nếu API chỉ trả về 'images/...' (ví dụ: 'http://localhost:8000/')
            const thumbnailUrl = mainImageObj ? `../../../../storage/${mainImageObj.image_url}` : '/images/no-image.png';

            return {
                ...product,
                // Tạo trường thumbnail giả để giao diện dùng được
                thumbnail: thumbnailUrl,
                // Nếu không có slug, dùng product_id làm slug tạm thời
                slug: product.slug || product.product_id,
                // Đảm bảo sku không bị undefined để tránh lỗi filter
                sku: product.sku || ''
            };
        });

    } catch (error) {
        console.error("Lỗi lấy sản phẩm:", error);
    }
};

const filteredProducts = computed(() => {
    if (!keyword.value.trim()) return [];
    const key = keyword.value.toLowerCase();
    return products.value.filter(p =>
        p.name.toLowerCase().includes(key) ||
        (p.sku && p.sku.toLowerCase().includes(key))
    );
});

const showBox = async () => {
    show.value = true;
    await nextTick();
    if (searchInputRef.value) searchInputRef.value.focus();
};

const closeBox = () => {
    show.value = false;
    keyword.value = ''; // Reset từ khóa
};

const clearSearch = () => {
    keyword.value = '';
    if (searchInputRef.value) searchInputRef.value.focus();
}

// Khi click vào kết quả tìm kiếm
const handleProductClick = () => {
    closeBox();
};

/* Đóng box khi đổi route (phòng trường hợp dùng nút back/forward browser) */
watch(() => route.fullPath, () => {
    closeBox();
});

/* ---------------- LIFECYCLE ---------------- */
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    handleFetchStockInCart();
    fetchProducts();
    emitter.on('update_stock-cart', handleFetchStockInCart);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    emitter.off('update_stock-cart', handleFetchStockInCart);
});
</script>

<template>
    <div class="container-fluid p-0">
        <div class="container">
            <div class="text-end color-primary mt-1 mb-0" v-if="auth.loggedIn">
                <span class="bg-secondary text-white px-3 py-1 rounded-5 fs-small">
                    Xin chào, {{ auth.user }}
                </span>
            </div>
        </div>

        <header class="py-3 header-sticky">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center position-relative">

                    <nav class="d-none d-lg-block">
                        <ul class="d-flex nav-items align-items-center">
                            <li class="nav-item-custom">
                                <router-link class="nav-link fw-bold" to="/products">
                                    Thời trang <i @click.stop.prevent="showBox" class="bi bi-chevron-down ms-1 text-primary cursor-pointer"></i>
                                </router-link>
                            </li>
                            <li class="nav-item-custom">
                                <span class="nav-link">Ưu đãi</span>
                            </li>
                            <li class="nav-item-custom"><span class="nav-link">Đồng phục</span></li>
                            <li class="nav-item-custom"><router-link to="/store" class="nav-link">Cửa hàng</router-link></li>
                            <li class="nav-item-custom"><router-link to="/blog" class="nav-link">Tin tức</router-link></li>
                        </ul>
                    </nav>

                    <div class="logo-center">
                        <router-link to="/">
                            <img src="../../../../public/images/logo_ocean.png" alt="Logo Ocean" class="img-fluid" style="max-width: 140px;">
                        </router-link>
                    </div>

                    <div class="end-items d-flex align-items-center gap-3">
                        <div class="search-trigger border rounded-5 px-3 py-2 d-flex align-items-center bg-light"
                             @click="showBox">
                            <i class="bi bi-search fs-6 me-2 text-secondary"></i>
                            <span class="text-secondary user-select-none">Tìm kiếm...</span>
                        </div>

                       <div class="user-actions d-flex align-items-center gap-3">
                            <div v-if="auth.loggedIn" class="d-flex align-items-center gap-3">
                                <router-link to="/carts" class="position-relative text-dark">
                                    <i class="bi bi-bag fs-4"></i>
                                    <div class="stock-cart" v-if="stockInCart > 0">
                                        <small>{{ stockInCart }}</small>
                                    </div>
                                </router-link>

                                <div class="user-dropdown-container" ref="dropdownRef">
                                    <span class="logged-in-icon" @click="handleDropdown">
                                        <i class="bi bi-person-circle fs-4 text-a"></i>
                                    </span>

                                    <transition name="fade">
                                        <div class="drop-down shadow-sm rounded" v-show="dropdown">
                                            <router-link to="/profile" class="dropdown-item">
                                                <i class="bi bi-person-vcard me-2"></i>Thông tin cá nhân
                                            </router-link>
                                            <router-link to="/admin" v-if="auth.role === 'admin'" class="dropdown-item">
                                                <i class="bi bi-gear me-2"></i>Quản trị
                                            </router-link>
                                            <div class="dropdown-divider"></div>
                                            <span @click="handleLogout" class="dropdown-item text-danger">
                                                <i class="bi bi-box-arrow-right me-2"></i>Đăng xuất
                                            </span>
                                        </div>
                                    </transition>
                                </div>
                            </div>

                            <router-link to="/login" v-else class="btn color-main d-flex align-items-center gap-2 px-3">
                                <i class="bi bi-person fs-5"></i> Đăng nhập
                            </router-link>
                        </div>

                    </div>
                </div>
            </div>
        </header>

        <transition name="slide-fade">
            <div class="search-overlay" v-show="show">
                <div class="search-container bg-white shadow-lg">
                    <div class="container py-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <router-link to="/" @click="closeBox">
                                <img src="../../../../public/images/logo_ocean.png" alt="logo" width="100">
                            </router-link>
                            <button class="btn btn-close" @click="closeBox"></button>
                        </div>

                        <div class="search-input-wrapper position-relative">
                            <i class="bi bi-search position-absolute text-muted" style="left: 15px; top: 50%; transform: translateY(-50%);"></i>
                            <input
                                type="text"
                                class="form-control form-control-lg rounded-pill ps-5 pe-5 shadow-none border-secondary-subtle"
                                placeholder="Bạn đang tìm gì hôm nay..."
                                v-model="keyword"
                                ref="searchInputRef"
                            >
                            <i v-if="keyword" @click="clearSearch" class="bi bi-x-circle-fill position-absolute text-secondary cursor-pointer" style="right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer;"></i>
                        </div>

                        <div class="search-results mt-4" v-if="keyword">
                            <h6 class="text-muted mb-3">Kết quả tìm kiếm:</h6>

                            <ul class="list-group list-group-flush result-list custom-scroll" v-if="filteredProducts.length">
                                <li v-for="p in filteredProducts" :key="p.product_id" class="list-group-item border-0 px-0">
                                    <router-link :to="`/products/${p.slug}`"
                                        class="d-flex align-items-center text-decoration-none text-dark search-item p-2 rounded"
                                        @click="handleProductClick">
                                        <div class="img-thumbnail-wrapper me-3">
                                            <img :src="p.thumbnail" alt="product" class="rounded object-fit-cover" width="60">
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="fw-bold text-truncate" style="max-width: 300px;">{{ p.name }}</div>
                                            <div class="text-primary fw-semibold">{{ Number(p.price).toLocaleString() }} đ</div>
                                        </div>
                                        <i class="bi bi-chevron-right text-muted"></i>
                                    </router-link>
                                </li>
                            </ul>

                            <div v-else class="text-center py-4 text-muted">
                                <i class="bi bi-emoji-frown fs-1 d-block mb-2"></i>
                                Không tìm thấy sản phẩm nào phù hợp với "<strong>{{ keyword }}</strong>"
                            </div>
                        </div>

                        <div v-else class="mt-4">
                            <div class="quick-nav d-flex justify-content-center gap-3 flex-wrap mb-4">
                                <a href="#" class="btn btn-light rounded-pill px-4 hover-primary">🆕 BST Thu Đông</a>
                                <router-link to="/store" class="btn btn-light rounded-pill px-4 hover-primary"><i class="bi bi-shop"></i> Cửa hàng</router-link>
                                <router-link to="/sale" class="btn btn-light rounded-pill px-4 hover-primary text-danger"><i class="bi bi-tags"></i> Ưu đãi</router-link>
                            </div>
                            <hr>
                             <CatgoriesListHeader />
                         </div>

                    </div>

                </div>
                <div class=" py-2 text-center">
                    <span @click="closeBox" class="btn btn-sm bg-white rounded-pill px-4">
                        Đóng <i class="bi bi-x-lg ms-1"></i>
                    </span>
                </div>
                <div class="overlay-backdrop" @click="closeBox"></div>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* --- General & Header --- */
.header-sticky {
    position: sticky;
    top: 0;
    z-index: 1020;
    background-color: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    box-shadow: 0 1px 10px rgba(0,0,0,0.05);
}

.logo-center {
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
}

.nav-items {
    list-style: none;
    margin: 0;
    padding: 0;
}

.nav-item-custom {
    margin-right: 20px;
    font-weight: 500;
    font-size: 15px;
}

.nav-item-custom a:hover,
.nav-item-custom span:hover {
    color: #3497E0;
    cursor: pointer;
}

.search-trigger {
    width: 200px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.search-trigger:hover {
    background-color: #e9ecef !important;
    border-color: #dee2e6 !important;
}

/* --- Cart Bubble --- */
.stock-cart {
    position: absolute;
    top: -5px;
    right: -8px;
    font-size: 11px;
    min-width: 18px;
    height: 18px;
    border-radius: 50%;
    background-color: #dc3545;
    color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    font-weight: bold;
    padding: 2px;
}

/* --- User Dropdown --- */
.user-dropdown-container {
    position: relative;
}

.logged-in-icon {
    cursor: pointer;
    transition: transform 0.2s;
}

.logged-in-icon:hover {
    transform: scale(1.1);
}

.drop-down {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 10px;
    background-color: white;
    min-width: 200px;
    z-index: 1030;
    padding: 8px 0;
    border: 1px solid rgba(0,0,0,0.08);
}

.dropdown-item {
    padding: 8px 20px;
    display: block;
    color: #333;
    text-decoration: none;
    /* font-size: px; */
    cursor: pointer;
    transition: background 0.2s;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #3497E0;
}

.dropdown-divider {
    height: 1px;
    background-color: #e9ecef;
    margin: 4px 0;
}

/* --- Search Overlay & Box --- */
.search-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100vh;
    z-index: 1050;
    display: flex;
    flex-direction: column;
}

.search-container {
    background: white;
    width: 100%;
    max-height: 85vh; /* Không chiếm hết màn hình */
    overflow-y: auto;
    position: relative;
    z-index: 1051;
    border-bottom-left-radius: 20px;
    border-bottom-right-radius: 20px;
}

.overlay-backdrop {
    flex-grow: 1;
    background-color: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(2px);
    cursor: pointer;
}

/* --- Search Results Customization --- */
.result-list {
    max-height: 380px; /* Giới hạn chiều cao danh sách kết quả */
    overflow-y: auto;
}

.search-item {
    transition: background-color 0.2s;
}

.search-item:hover {
    background-color: #f1f8ff;
}

/* Custom Scrollbar for results */
.custom-scroll::-webkit-scrollbar {
    width: 6px;
}
.custom-scroll::-webkit-scrollbar-track {
    background: #f1f1f1;
}
.custom-scroll::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
.custom-scroll::-webkit-scrollbar-thumb:hover {
    background: #aaa;
}

.hover-primary:hover {
    background-color: #3497E0 !important;
    color: white !important;
}

/* --- Vue Transitions --- */
/* Fade for dropdown */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.2s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Slide Fade for Search Box */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.3s ease-out;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
  transform: translateY(-20px);
  opacity: 0;
}
</style>
