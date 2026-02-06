<script setup>
/* --- GIỮ NGUYÊN PHẦN SCRIPT CŨ CỦA BẠN --- */
import { ref, onMounted, watch, reactive, computed, inject } from "vue";
import { useRoute, useRouter } from "vue-router";
import { Toast } from "bootstrap";
import api from "../../axios";
import { emitter } from "../../stores/eventBus";
import BoxProduct from "../../components/client/BoxProduct.vue";

const route = useRoute();
const router = useRouter();
const { auth } = inject("auth");
const productId = ref(route.params.id);

// Modal đăng nhập
const showLoginModal = ref(false);
// products related
const productRelated = ref([]);
const handleFetchProductsRelated = async (id) => {
    try {
        const res = await api.get(`/products/${id}/related`);
        if (res.status === 200) {
            productRelated.value = res.data?.data;
        }
    } catch (err) {
        console.log("Loi khi goi APi: ", err);
    }
};
// Product Detail
const productDetail = ref(null);
const handleFetchProductDetailById = async (id) => {
    try {
        const res = await api.get(`products/${id}`);
        productDetail.value = res.status === 200 ? res.data : null;
    } catch (err) {
        console.error("Lỗi khi tải chi tiết sản phẩm:", err);
        productDetail.value = null;
    }
};

/* --------------------------------------------------
    ⭐⭐  BÌNH LUẬN + ĐÁNH GIÁ (FIX ĐÚNG LOGIC)
--------------------------------------------------- */
const reviews = ref({
    product_id: null,
    total_reviews: 0,
    reviews: [],
});
const myReview = ref(null);

const hasReviewed = computed(() => !!myReview.value);

const reviewForm = reactive({
    rating: 0,
    content: "",
});

// ===== MODAL STATES =====
const showReviewedModal = ref(false);
const showEditModal = ref(false);
const showDeleteModal = ref(false);

// ===== EDIT REVIEW =====
const editReview = reactive({
    review_id: null,
    rating: 0,
    content: "",
});

const deleteReviewId = ref(null);

// ===== AVG RATING =====
const avgRating = computed(() => {
    if (!reviews.value?.reviews?.length) return 0;
    const sum = reviews.value.reviews.reduce((t, r) => t + r.rating, 0);
    return Number((sum / reviews.value.reviews.length).toFixed(1));
});

// ⭐ FILTER BY RATING
const selectedRating = ref(0); // 0 = tất cả

const ratingStats = computed(() => {
    const stats = { 1: 0, 2: 0, 3: 0, 4: 0, 5: 0 };
    if(reviews.value && reviews.value.reviews) {
         reviews.value.reviews.forEach((r) => {
            stats[r.rating]++;
        });
    }
    return stats;
});
// Tính phần trăm cho thanh bar
const getRatingPercent = (star) => {
    if (!reviews.value.total_reviews) return 0;
    return (ratingStats.value[star] / reviews.value.total_reviews) * 100;
};

const filteredReviews = computed(() => {
    if (selectedRating.value === 0) {
        return reviews.value.reviews;
    }
    return reviews.value.reviews.filter(
        (r) => r.rating === selectedRating.value
    );
});

// ===== FETCH REVIEWS =====
const fetchReviews = async () => {
    try {
        const res = await api.get(`/products/${productId.value}/reviews`);
        reviews.value = res.data;
    } catch (err) {
        console.error("Lỗi tải review:", err);
        reviews.value = {
            product_id: productId.value,
            total_reviews: 0,
            reviews: [],
        };
    }
};

// ===== CHECK USER REVIEW =====
watch(reviews, () => {
    if (!auth?.loggedIn || !reviews.value?.reviews) {
        myReview.value = null;
        return;
    }

    myReview.value =
        reviews.value.reviews.find((r) => r.user?.email === auth.email) ||
        null;
});

// ===== SUBMIT REVIEW (CHỈ ĐƯỢC TẠO) =====
const handleSubmitReview = async () => {
    if (!auth?.loggedIn) {
        showLoginModal.value = true;
        return;
    }

    if (myReview.value) {
        showReviewedModal.value = true;
        return;
    }

    if (!reviewForm.rating) {
        alert("Vui lòng chọn số sao!");
        return;
    }

    try {
        await api.post("reviews", {
            product_id: productId.value,
            ...reviewForm,
        });
        reviewForm.rating = 0;
        reviewForm.content = "";
        fetchReviews();
    } catch (err) {
        console.error(err);
    }
};

// ===== EDIT REVIEW =====
const openEditModal = (r) => {
    editReview.review_id = r.review_id;
    editReview.rating = r.rating;
    editReview.content = r.content;
    showEditModal.value = true;
};

const submitEditReview = async () => {
    try {
        await api.put(`reviews/${editReview.review_id}`, {
            rating: editReview.rating,
            content: editReview.content,
        });
        showEditModal.value = false;
        fetchReviews();
    } catch (err) {
        console.error(err);
    }
};

// ===== DELETE REVIEW =====
const openDeleteModal = (id) => {
    deleteReviewId.value = id;
    showDeleteModal.value = true;
};

const confirmDeleteReview = async () => {
    try {
        await api.delete(`reviews/${deleteReviewId.value}`);
        showDeleteModal.value = false;
        deleteReviewId.value = null;
        fetchReviews();
    } catch (err) {
        console.error(err);
    }
};

const activeMenu = ref(null);

const toggleMenu = (id) => {
    activeMenu.value = activeMenu.value === id ? null : id;
};

/* --------------------------------------------------
    🎨  SẢN PHẨM (MÀU / SIZE)
--------------------------------------------------- */
const selectedColor = ref(null);
const selectedSize = ref(null);

const uniqueVariants = computed(() => {
    if (!productDetail.value) return [];
    const seen = new Set();
    return productDetail.value.variant.filter((v) => {
        const isDup = seen.has(v.color);
        seen.add(v.color);
        return !isDup;
    });
});

watch(
    uniqueVariants,
    (v) => {
        if (v.length > 0 && !selectedColor.value)
            selectedColor.value = v[0].color;
    },
    { immediate: true }
);

const uniqueSizes = computed(() => {
    if (!productDetail.value || !selectedColor.value) return [];
    return productDetail.value.variant.filter(
        (v) => v.color === selectedColor.value
    );
});

watch(
    selectedColor,
    () => {
        if (uniqueSizes.value.length > 0)
            selectedSize.value = uniqueSizes.value[0].size;
    },
    { immediate: true }
);

/* --------------------------------------------------
    🎨  ẢNH (LOGIC MỚI)
--------------------------------------------------- */

// Lọc ảnh theo màu sắc đang chọn
const imagesBySelectedColor = computed(() => {
    if (!productDetail.value || !selectedColor.value) return [];

    // 1. Lấy danh sách ID biến thể của màu đang chọn
    const variantIdsOfColor = productDetail.value.variant
        .filter((v) => v.color === selectedColor.value)
        .map((v) => v.variant_id);

    // 2. Lọc ảnh có variant_id nằm trong danh sách trên
    const rawImages = productDetail.value.image.filter((img) =>
        variantIdsOfColor.includes(img.variant_id)
    );

    // 3. Khử trùng lặp URL (nếu nhiều size dùng chung ảnh)
    const seen = new Set();
    return rawImages.filter((img) => {
        const isDup = seen.has(img.image_url);
        seen.add(img.image_url);
        return !isDup;
    });
});

const currentDisplayImage = ref(null);

// Tự động đổi ảnh hiển thị khi list ảnh thay đổi (do đổi màu)
watch(
    imagesBySelectedColor,
    (newImages) => {
        if (newImages && newImages.length > 0) {
            // Ưu tiên ảnh is_main = 1, nếu không thì lấy cái đầu tiên
            const main = newImages.find((i) => i.is_main === 1) || newImages[0];
            currentDisplayImage.value = main.image_url;
        }
    },
    { immediate: true }
);

/* --------------------------------------------------
    🛒  CART
--------------------------------------------------- */
const cartValue = reactive({ count: 1, variant_id: null });
const selectedVariant = computed(() => {
    if (!productDetail.value) return null;
    return productDetail.value.variant.find(
        (v) => v.color === selectedColor.value && v.size === selectedSize.value
    );
});
watch(
    selectedVariant,
    (newV) => {
        cartValue.variant_id = newV ? newV.variant_id : null;
    },
    { immediate: true }
);

const showToast = (message, type = "success") => {
    const toastEl = document.getElementById("cartToast");
    toastEl.classList.remove("text-bg-success", "text-bg-danger");
    toastEl.classList.add(`text-bg-${type}`);
    toastEl.querySelector(".toast-body").innerText = message;
    new Toast(toastEl).show();
};

const handleAddToCart = async () => {
    if (!cartValue.variant_id)
        return showToast("Vui lòng chọn biến thể!", "danger");
    if (!auth?.loggedIn) return (showLoginModal.value = true);
    try {
        const res = await api.post("carts", cartValue);
        if (res.status === 200) {
            showToast("Thêm vào giỏ hàng thành công!");
            cartValue.count = 1;
        }
        emitter.emit("update_stock-cart");
    } catch (err) {
        showToast("Có lỗi xảy ra!", "danger");
    }
};

/* --------------------------------------------------
    🚪 LOGIN MODAL & MOUNT
--------------------------------------------------- */
const goToLogin = () => {
    showLoginModal.value = false;
    router.push("/login?back=products/" + productId.value);
};
const closeModal = () => (showLoginModal.value = false);

const resetState = () => {
    productDetail.value = null;
    selectedColor.value = null;
    selectedSize.value = null;
    currentDisplayImage.value = null;
    reviews.value = {
        product_id: productId.value,
        total_reviews: 0,
        reviews: [],
    };
    myReview.value = null;
    cartValue.count = 1;
};

watch(
    () => route.params.id,
    (id) => {
        productId.value = id;
        resetState();
        selectedRating.value = 0;
        handleFetchProductDetailById(id);
        handleFetchProductsRelated(id);
        fetchReviews();
        showDescription.value = false;
    }
);

onMounted(() => {
    handleFetchProductDetailById(productId.value);
    handleFetchProductsRelated(productId.value);
    fetchReviews();
});
const formatDate = (dateString) => {
    const d = new Date(dateString);
    return d.toLocaleString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
};

const showSizeGuide = ref(false);
const sizeGuideTab = ref('men');
const activeTab = ref('description');
</script>

<template>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="cartToast" class="toast text-bg-success border-0 shadow" role="alert">
            <div class="d-flex align-items-center">
                <div class="toast-body fw-light fs-6">Thông báo hệ thống</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <div v-if="showLoginModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center">
        <div class="bg-white rounded-3 shadow-lg p-4" style="max-width: 350px; width: 90%;">
            <div class="text-center mb-3">
                <i class="bi bi-person-lock fs-1 text-theme"></i>
                <h5 class="fw-normal mt-2">Cần đăng nhập</h5>
                <p class="text-muted fw-light small">Vui lòng đăng nhập để mua hàng hoặc đánh giá.</p>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-light flex-fill rounded-1 fw-light" @click="closeModal">Đóng</button>
                <button class="btn btn-theme flex-fill rounded-1 fw-light text-white" @click="goToLogin">Đăng nhập</button>
            </div>
        </div>
    </div>

    <!-- Size Guide Modal -->
    <div v-if="showSizeGuide" class="modal-backdrop-custom d-flex align-items-center justify-content-center" @click.self="showSizeGuide = false">
        <div class="bg-white rounded-4 shadow-lg overflow-hidden animate__animated animate__fadeInDown" style="max-width: 600px; width: 95%;">
            <div class="d-flex justify-content-between align-items-center p-3 bg-light border-bottom">
                <h5 class="mb-0 fw-bold text-theme"><i class="bi bi-ruler me-2"></i>Hướng dẫn chọn size</h5>
                <button type="button" class="btn-close" @click="showSizeGuide = false"></button>
            </div>
            <div class="p-4">
                <div class="d-flex justify-content-center gap-2 mb-4">
                    <button class="btn btn-sm rounded-pill px-4 fw-medium" :class="sizeGuideTab === 'men' ? 'btn-theme text-white' : 'btn-outline-secondary'" @click="sizeGuideTab = 'men'">Nam</button>
                    <button class="btn btn-sm rounded-pill px-4 fw-medium" :class="sizeGuideTab === 'women' ? 'btn-theme text-white' : 'btn-outline-secondary'" @click="sizeGuideTab = 'women'">Nữ</button>
                    <button class="btn btn-sm rounded-pill px-4 fw-medium" :class="sizeGuideTab === 'kids' ? 'btn-theme text-white' : 'btn-outline-secondary'" @click="sizeGuideTab = 'kids'">Trẻ em</button>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered text-center table-hover align-middle" v-if="sizeGuideTab === 'men'">
                        <thead class="bg-light text-secondary"><tr><th>Size</th><th>Chiều cao (m)</th><th>Cân nặng (kg)</th></tr></thead>
                        <tbody>
                            <tr><td class="fw-bold">S</td><td>1.50 - 1.60</td><td>42 - 49</td></tr>
                            <tr><td class="fw-bold">M</td><td>1.60 - 1.67</td><td>50 - 55</td></tr>
                            <tr><td class="fw-bold">L</td><td>1.67 - 1.70</td><td>56 - 65</td></tr>
                            <tr><td class="fw-bold">XL</td><td>1.70 - 1.75</td><td>66 - 71</td></tr>
                            <tr><td class="fw-bold">XXL</td><td>1.75 - 1.80</td><td>72 - 76</td></tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered text-center table-hover align-middle" v-if="sizeGuideTab === 'women'">
                        <thead class="bg-light text-secondary"><tr><th>Size</th><th>Chiều cao (m)</th><th>Cân nặng (kg)</th></tr></thead>
                        <tbody>
                            <tr><td class="fw-bold">S</td><td>1.48 - 1.53</td><td>38 - 43</td></tr>
                            <tr><td class="fw-bold">M</td><td>1.53 - 1.55</td><td>43 - 46</td></tr>
                            <tr><td class="fw-bold">L</td><td>1.53 - 1.58</td><td>46 - 53</td></tr>
                            <tr><td class="fw-bold">XL</td><td>1.55 - 1.62</td><td>53 - 57</td></tr>
                        </tbody>
                    </table>
                    <table class="table table-bordered text-center table-hover align-middle" v-if="sizeGuideTab === 'kids'">
                        <thead class="bg-light text-secondary"><tr><th>Size</th><th>Cân nặng (kg)</th><th>Độ tuổi (tham khảo)</th></tr></thead>
                        <tbody>
                            <tr><td class="fw-bold">2</td><td>10 - 12</td><td>1 - 2 tuổi</td></tr>
                            <tr><td class="fw-bold">4</td><td>13 - 15</td><td>3 - 4 tuổi</td></tr>
                            <tr><td class="fw-bold">6</td><td>16 - 20</td><td>5 - 6 tuổi</td></tr>
                            <tr><td class="fw-bold">8</td><td>21 - 26</td><td>7 - 8 tuổi</td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="mt-3 text-muted small fst-italic text-center">
                    * Bảng size chỉ mang tính chất tham khảo. Vui lòng liên hệ để được tư vấn chi tiết.
                </div>
            </div>
        </div>
    </div>

    <div class="container py-5" v-if="productDetail">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><RouterLink to="/" class="text-decoration-none text-muted fw-light">Trang chủ</RouterLink></li>
                <li class="breadcrumb-item"><router-link to="/products" class="text-decoration-none text-muted fw-light">Sản phẩm</router-link></li>
                <li class="breadcrumb-item active fw-light" aria-current="page">{{ productDetail.name }}</li>
            </ol>
        </nav>

        <div class="row g-5">
            <div class="col-lg-7">
                <div class="d-flex flex-column gap-3">
                    <div class="bg-white rounded-4 shadow-sm  d-flex align-items-center justify-content-center overflow-hidden position-relative main-image-box p-3">
                        <img v-if="currentDisplayImage" :src="`../../../../storage/${currentDisplayImage}`"
                            class="img-fluid object-fit-contain px-5 " width="80%"/>
                    </div>
                    <div class="d-flex gap-2 overflow-auto hide-scrollbar">
                        <img v-for="img in imagesBySelectedColor" :key="img.image_id"
                            :src="`../../../../storage/${img.image_url}`"
                            @click="currentDisplayImage = img.image_url"
                            class="rounded-3 cursor-pointer border transition-all shadow-sm"
                            :class="currentDisplayImage === img.image_url ? 'border-theme opacity-100 ring-2' : 'border-light opacity-75'"
                            style="width: 80px; object-fit: cover;" />
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="sticky-top" style="top: 20px; z-index: 10;">
                    <h1 class="display-6 fw-light mb-2">{{ productDetail.name }}</h1>

                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="text-theme fs-2 fw-bold">{{ productDetail.price.toLocaleString() }}₫</div>
                        <div class="vr bg-secondary opacity-25"></div>
                        <div class="d-flex align-items-center cursor-pointer" @click="activeTab = 'reviews'; document.getElementById('product-tabs').scrollIntoView({ behavior: 'smooth' })">
                            <i class="bi bi-star-fill text-warning me-1"></i>
                            <span class="fw-bold me-1">{{ avgRating }}</span>
                            <span class="text-muted fw-light small">({{ reviews.total_reviews }} đánh giá)</span>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2 fw-normal small text-uppercase text-muted ls-1">Màu sắc</p>
                        <div class="d-flex gap-2">
                            <button v-for="v in uniqueVariants" :key="v.variant_id"
                                class="color-swatch rounded-circle position-relative"
                                :class="{ 'active': v.color === selectedColor }"
                                :style="{ backgroundColor: v.color }"
                                @click="selectedColor = v.color">
                                <i v-if="v.color === selectedColor" class="bi bi-check text-white position-absolute top-50 start-50 translate-middle small"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <p class="mb-0 fw-normal small text-uppercase text-muted ls-1">Kích thước</p>
                            <button class="btn btn-link p-0 text-decoration-none small text-theme fw-medium" @click="showSizeGuide = true">
                                <i class="bi bi-ruler me-1"></i>Hướng dẫn chọn size
                            </button>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button v-for="v in uniqueSizes" :key="v.variant_id"
                                class="btn btn-sm btn-outline-secondary rounded-5 fw-bold text-black min-w-50"
                                :class="{ 'btn-theme-active': v.size === selectedSize }"
                                @click="selectedSize = v.size">
                                {{ v.size }}
                            </button>
                        </div>
                    </div>

                    <div class="d-grid gap-3">
                        <div class="row g-2">
                            <div class="col-4">
                                <div class="input-group input-group-lg h-100">
                                    <button class="btn btn-outline-light text-dark border-secondary border-opacity-25" type="button"
                                        @click="cartValue.count--" :disabled="cartValue.count <= 1">-</button>
                                    <input type="text" class="form-control text-center border-secondary border-opacity-25 fw-light"
                                        :value="cartValue.count" readonly>
                                    <button class="btn btn-outline-light text-dark border-secondary border-opacity-25" type="button"
                                        @click="cartValue.count++">+</button>
                                </div>
                            </div>
                            <div class="col-8">
                                <button class="btn btn-theme text-white w-100 h-100 fw-normal fs-5 rounded-1 shadow-sm d-flex align-items-center justify-content-center gap-2"
                                    @click="handleAddToCart">
                                    <i class="bi bi-bag-plus"></i> Mua ngay
                                </button>
                            </div>
                        </div>
                        <div class="text-muted fw-light small text-center">
                            <i class="bi bi-shield-check me-1"></i> Chính sách đổi trả trong 30 ngày
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5" id="product-tabs">
            <div class="col-12">
                <ul class="nav nav-tabs nav-fill border-bottom-0 mb-4 gap-2" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-uppercase ls-1 py-3 rounded-top-3"
                            :class="{ 'active border-bottom border-theme border-3 text-theme bg-light': activeTab === 'description', 'text-muted border-bottom': activeTab !== 'description' }"
                            @click="activeTab = 'description'">
                            Chi tiết sản phẩm
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-uppercase ls-1 py-3 rounded-top-3"
                            :class="{ 'active border-bottom border-theme border-3 text-theme bg-light': activeTab === 'reviews', 'text-muted border-bottom': activeTab !== 'reviews' }"
                            @click="activeTab = 'reviews'">
                            Đánh giá khách hàng ({{ reviews.total_reviews }})
                        </button>
                    </li>
                </ul>

                <div class="tab-content bg-white p-4 rounded-3 shadow-sm border" style="min-height: 300px;">
                    <!-- TAB DESCRIPTION -->
                    <div v-if="activeTab === 'description'" class="fade show active animate__animated animate__fadeIn">
                        <div class="fw-light text-secondary lh-lg" v-html="productDetail.description || 'Đang cập nhật...'"></div>
                </div>

                    <!-- TAB REVIEWS -->
                    <div v-if="activeTab === 'reviews'" class="fade show active animate__animated animate__fadeIn">
                        <div class="bg-light bg-opacity-50 rounded-3 p-4 mb-4 border">
                        <div class="row align-items-center g-4">
                            <div class="col-md-4 text-center border-end-md">
                                <div class="display-3 fw-light text-theme">{{ avgRating }}</div>
                                <div class="text-warning fs-5 mb-1">
                                    <i v-for="n in 5" :key="n" class="bi" :class="n <= Math.floor(avgRating) ? 'bi-star-fill' : 'bi-star'"></i>
                                </div>
                                <div class="text-muted fw-light small">{{ reviews.total_reviews }} nhận xét</div>
                            </div>

                            <div class="col-md-8">
                                <div class="d-flex flex-column gap-2">
                                    <div v-for="star in [5, 4, 3, 2, 1]" :key="star" class="d-flex align-items-center gap-2">
                                        <span class="fw-light small text-muted text-nowrap" style="width: 30px;">{{ star }} <i class="bi bi-star-fill text-warning"></i></span>
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-theme" role="progressbar"
                                                :style="{ width: getRatingPercent(star) + '%' }"></div>
                                        </div>
                                        <span class="fw-light small text-muted text-end" style="width: 35px;">{{ ratingStats[star] }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-4 pt-3 border-top">
                             <div class="d-flex gap-2">
                                <button class="btn btn-sm rounded-pill fw-light"
                                    :class="selectedRating === 0 ? 'btn-theme text-white' : 'btn-outline-secondary'"
                                    @click="selectedRating = 0">Tất cả</button>
                                <button v-for="star in [5, 4, 3, 2, 1]" :key="star"
                                    class="btn btn-sm rounded-pill fw-light"
                                    :class="selectedRating === star ? 'btn-theme text-white' : 'btn-outline-secondary'"
                                    @click="selectedRating = star">{{ star }} sao</button>
                             </div>
                         </div>
                    </div>

                    <div class="d-flex flex-column gap-3">
                        <div v-if="filteredReviews.length === 0" class="text-center py-4 text-muted fw-light">
                            Chưa có đánh giá nào.
                        </div>
                        <div v-for="r in filteredReviews" :key="r.review_id" class="border-bottom pb-3">
                            <div class="d-flex gap-3">
                                <div class="rounded-circle bg-light d-flex align-items-center justify-content-center text-secondary fw-normal"
                                    style="width: 40px; height: 40px; font-size: 14px;">
                                    {{ r.user.name.substring(0,2).toUpperCase() }}
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <span class="fw-normal text-dark">{{ r.user.name }}</span>
                                            <div class="text-warning fs-6">
                                                <i v-for="n in 5" :key="n" class="bi" :class="n <= r.rating ? 'bi-star-fill' : 'bi-star'"></i>
                                            </div>
                                        </div>
                                        <small class="text-muted fw-light">{{ formatDate(r.created_at) }}</small>
                                    </div>
                                    <p class="mt-2 mb-1 fw-light text-secondary">{{ r.content }}</p>

                                    <div v-if="auth.loggedIn && r.user?.email === auth.email" class="d-flex gap-3">
                                        <span class="text-theme fw-light small cursor-pointer" @click="openEditModal(r)">Sửa</span>
                                        <span class="text-danger fw-light small cursor-pointer" @click="openDeleteModal(r.review_id)">Xóa</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                     </div>
                    </div>

                </div>
            </div>
        </div>

        <div class="mt-5 pt-5">
            <h4 class="fw-normal mb-4 text-center">Sản phẩm tương tự</h4>
            <div class="row g-3">
                 <BoxProduct v-for="product in productRelated" :key="product.product_id" :product="product"
                    class="col-6 col-md-4 col-lg-3" />
            </div>
        </div>
    </div>

    <div v-if="showEditModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center">
        <div class="bg-white rounded-3 shadow p-4 w-100" style="max-width: 500px;">
            <h5 class="fw-normal mb-3">Cập nhật đánh giá</h5>
            <div class="mb-3 text-center">
                <i v-for="n in 5" :key="n" class="bi fs-3 cursor-pointer mx-1"
                   :class="n <= editReview.rating ? 'bi-star-fill text-warning' : 'bi-star text-secondary'"
                   @click="editReview.rating = n"></i>
            </div>
            <textarea v-model="editReview.content" class="form-control fw-light mb-3" rows="3"></textarea>
            <div class="d-flex justify-content-end gap-2">
                <button class="btn btn-light rounded-1 fw-light" @click="showEditModal = false">Hủy</button>
                <button class="btn btn-theme text-white rounded-1 fw-light" @click="submitEditReview">Lưu thay đổi</button>
            </div>
        </div>
    </div>

     <div v-if="showDeleteModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center">
        <div class="bg-white rounded-3 shadow p-4 text-center" style="max-width: 350px;">
            <p class="fw-light mb-4">Bạn có chắc chắn muốn xóa đánh giá này không?</p>
            <div class="d-flex justify-content-center gap-2">
                <button class="btn btn-light rounded-1 fw-light" @click="showDeleteModal = false">Không</button>
                <button class="btn btn-danger rounded-1 fw-light" @click="confirmDeleteReview">Xóa</button>
            </div>
        </div>
    </div>
     <div v-if="showReviewedModal" class="modal-backdrop-custom d-flex align-items-center justify-content-center">
        <div class="bg-white rounded-3 shadow p-4 text-center">
            <p class="fw-light mb-3">Bạn đã đánh giá sản phẩm này rồi.</p>
            <button class="btn btn-theme text-white rounded-1 fw-light px-4" @click="showReviewedModal = false">OK</button>
        </div>
    </div>

</template>

<style scoped>
/* --- THEME CONFIGURATION --- */
:root {
    --theme-color: #3497e0;
    --theme-hover: #2d86c9;
}

/* Utilities */
.text-theme { color: #3497e0 !important; }
.bg-theme { background-color: #3497e0 !important; }
.btn-theme {
    background-color: #3497e0;
    border-color: #3497e0;
    transition: all 0.3s ease;
}
.btn-theme:hover {
    background-color: #2d86c9;
    border-color: #2d86c9;
}
.btn-outline-theme {
    color: #3497e0;
    border-color: #3497e0;
}
.btn-outline-theme:hover {
    background-color: #3497e0;
    color: #fff;
}
.border-theme { border-color: #3497e0 !important; }

.cursor-pointer { cursor: pointer; }
.transition-all { transition: all 0.2s; }
.ls-1 { letter-spacing: 1px; }
.min-w-50 { min-width: 50px; }
.opacity-60 { opacity: 0.6; }
.hide-scrollbar::-webkit-scrollbar { display: none; }
.scroll-mt-5 { scroll-margin-top: 3rem; }

/* Custom Elements */
.modal-backdrop-custom {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4); z-index: 1050; backdrop-filter: blur(2px);
}

.color-swatch {
    width: 30px; height: 30px; border: 1px solid #ddd;
    transition: transform 0.2s; cursor: pointer;
}
.color-swatch:hover { transform: scale(1.1); }
.color-swatch.active { border: 2px solid #3497e0; transform: scale(1.1); }

.btn-theme-active {
    background-color: #3497e0 !important;
    color: white !important;
    border-color: #3497e0 !important;
}
.ring-2 {
    box-shadow: 0 0 0 2px #3497e0;
}

/* Responsive Tweaks */
@media (min-width: 768px) {
    .border-end-md { border-right: 1px solid #eee; }
}
</style>
