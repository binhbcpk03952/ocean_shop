<script setup>
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

    reviews.value.reviews.forEach((r) => {
        stats[r.rating]++;
    });

    return stats;
});

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
    // productDetail.value = null
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

// dropdown chi tiet san pham
const showDescription = ref(false);
</script>

<template>
    <div
        class="toast-container position-fixed top-0 end-0 p-3"
        style="z-index: 9999"
    >
        <div id="cartToast" class="toast text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Thông báo</div>
                <button
                    type="button"
                    class="btn-close btn-close-white me-2 m-auto"
                    data-bs-dismiss="toast"
                ></button>
            </div>
        </div>
    </div>

    <div
        v-if="showLoginModal"
        class="modal fade show d-block"
        style="background: rgba(0, 0, 0, 0.5)"
        tabindex="-1"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-header">
                    <h5 class="modal-title">Thông báo</h5>
                    <button
                        type="button"
                        class="btn-close"
                        @click="closeModal"
                    ></button>
                </div>
                <div class="modal-body text-center">
                    Vui lòng đăng nhập để thực hiện hành động này.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeModal">
                        Hủy</button
                    ><button class="btn btn-primary" @click="goToLogin">
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="container" v-if="productDetail">
        <div class="row">
            <div class="col-lg-6 p-5">
                <div class="d-flex gap-3 h-100">
                    <div class="d-flex flex-column gap-2 thumbnail-scroll">
                        <img
                            v-for="img in imagesBySelectedColor"
                            :key="img.image_id"
                            :src="`../../../../storage/${img.image_url}`"
                            @click="currentDisplayImage = img.image_url"
                            class="border"
                            :class="{
                                'border-main':
                                    currentDisplayImage === img.image_url,
                            }"
                            style="
                                width: 70px;
                                height: auto;
                                object-fit: cover;
                                cursor: pointer;
                            "
                        />
                    </div>

                    <div
                        class="flex-grow-1 d-flex align-items-center justify-content-center main-img-container"
                    >
                        <img
                            v-if="currentDisplayImage"
                            :src="`../../../../storage/${currentDisplayImage}`"
                            class="img-fluid rounded-1"
                            style="max-height: 450px; object-fit: contain"
                        />
                    </div>
                </div>
            </div>

            <div class="col-lg-6 p-5">
                <div class="fs-4 fw-bold text-danger">
                    {{ productDetail.price.toLocaleString() }}₫
                </div>
                <div class="fs-3 fw-semibold mb-3">
                    {{ productDetail.name }}
                </div>

                <div
                    class="d-flex align-items-center gap-2 mb-3"
                    v-if="reviews"
                >
                    <span
                        class="star"
                        v-for="n in 5"
                        :key="n"
                        :class="{ active: n <= Math.floor(avgRating) }"
                        >★</span
                    >
                    <span class="text-muted ms-1">
                        {{ avgRating }} ({{ reviews.total_reviews }})
                    </span>
                </div>

                <div class="mt-3">
                    <span>Màu sắc: </span>
                    <div class="d-flex mt-2">
                        <button
                            v-for="v in uniqueVariants"
                            :key="v.variant_id"
                            class="btn-color"
                            :class="{ color_active: v.color === selectedColor }"
                            :style="{ background: v.color }"
                            @click="selectedColor = v.color"
                        ></button>
                    </div>
                </div>

                <div class="mt-3">
                    <span>Kích thước: </span>
                    <div class="d-flex mt-2">
                        <button
                            v-for="v in uniqueSizes"
                            :key="v.variant_id"
                            class="btn-color"
                            style="background: transparent"
                            :class="{ color_active: v.size === selectedSize }"
                            @click="selectedSize = v.size"
                        >
                            {{ v.size }}
                        </button>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-4">
                        <div
                            class="border rounded-4 d-flex justify-content-between px-3 py-2"
                        >
                            <button
                                class="btn-custom"
                                @click="cartValue.count--"
                                :disabled="cartValue.count <= 1"
                            >
                                <i class="bi bi-dash"></i>
                            </button>
                            <span>{{ cartValue.count }}</span>
                            <button
                                class="btn-custom"
                                @click="cartValue.count++"
                            >
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="col-8">
                        <button
                            class="btn btn-primary w-100 py-2 rounded-4"
                            @click="handleAddToCart"
                        >
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-5 border p-3 rounded-3">
            <div class="d-flex justify-content-between">
                <h3 class="color-main">Mô tả sản phẩm</h3>
                <i
                    class="bi bi-plus-circle-fill fs-3 color-main"
                    @click="showDescription = !showDescription"
                ></i>
            </div>

            <!-- Transition -->
            <transition name="fade-slide">
                <p v-if="showDescription" class="mt-4">
                    {{ productDetail.description }}
                </p>
            </transition>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8" >
                <h3 class="mb-4">Đánh giá sản phẩm</h3>
                <div class="card p-3 mb-4" v-if="!hasReviewed">
                    <h5>
                        {{
                            myReview ? "Sửa đánh giá của bạn" : "Viết đánh giá"
                        }}
                    </h5>
                    <div class="d-flex gap-1 mt-2">
                        <span
                            v-for="n in 5"
                            :key="n"
                            class="star selectable"
                            :class="{ active: n <= reviewForm.rating }"
                            @click="reviewForm.rating = n"
                            >★</span
                        >
                    </div>
                    <textarea
                        v-model="reviewForm.content"
                        class="form-control mt-3"
                        rows="3"
                        placeholder="Chia sẻ cảm nhận..."
                    ></textarea>
                    <button
                        class="btn btn-primary mt-3 w-100"
                        @click="handleSubmitReview"
                        :disabled="hasReviewed"
                    >
                        {{
                            hasReviewed
                                ? "Bạn đã đánh giá sản phẩm này"
                                : "Gửi đánh giá"
                        }}
                    </button>
                </div>
                <div v-if="hasReviewed" class="alert alert-warning mt-2">
                    Bạn đã đánh giá sản phẩm này rồi. Bạn chỉ có thể
                    <b>sửa</b> hoặc <b>xóa</b> đánh giá của mình.
                </div>
                <div class="mb-3">
                    <button
                        class="btn btn-sm me-2"
                        :class="selectedRating === 0 ? 'btn-dark': 'btn-outline-dark'"
                        @click="selectedRating = 0"
                    >
                        Tất cả ({{ reviews.total_reviews }})
                    </button>

                    <button
                        v-for="star in [5, 4, 3, 2, 1]"
                        :key="star"
                        class="btn btn-sm me-2"
                        :class="
                            selectedRating === star
                                ? 'btn-warning'
                                : 'btn-outline-warning'
                        "
                        @click="selectedRating = star"
                    >
                        {{ star }} ★ ({{ ratingStats[star] }})
                    </button>
                </div>

                <div class="card p-3 mb-5">
                    <h5 class="mb-3">Tất cả đánh giá</h5>
                    <hr class="review-divider" />
                    <div
                        v-if="!reviews || reviews.reviews?.length === 0"
                        class="text-muted"
                    >
                        Chưa có đánh giá nào.
                    </div>
                    <div
                        v-for="r in filteredReviews"
                        :key="r.review_id"
                        class="review-item"
                    >
                        <div class="review-user">{{ r.user.name }}</div>
                        <div class="review-stars">
                            <span
                                v-for="n in 5"
                                :key="n"
                                class="star"
                                :class="{ active: n <= r.rating }"
                                >★</span
                            >
                        </div>
                        <div class="review-content">{{ r.content }}</div>
                        <div class="review-date">
                            {{ formatDate(r.created_at) }}
                        </div>
                        <div
                            v-if="
                                auth.loggedIn && r.user?.email === auth.email
                            "
                            class="mt-2 d-flex gap-2"
                        >
                            <button
                                class="btn border-0 btn-sm color-main fw-bold"
                                @click="openEditModal(r)"
                            >
                                Sửa
                            </button>

                            <button
                                class="btn btn-sm text-danger fw-bold"
                                @click="openDeleteModal(r.review_id)"
                            >
                                Xóa
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <h2>Sản phẩm liên quan</h2>
            <BoxProduct
                v-for="product in productRelated"
                :key="product.product_id"
                :product="product"
                class="col-6 col-md-4 col-lg-3"
            />
        </div>
    </div>
    <!-- MODAL: ĐÃ ĐÁNH GIÁ -->
    <div
        v-if="showReviewedModal"
        class="modal fade show d-block"
        style="background: rgba(0, 0, 0, 0.5)"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Thông báo</h5>
                    <button
                        class="btn-close"
                        @click="showReviewedModal = false"
                    ></button>
                </div>
                <div class="modal-body">Bạn đã đánh giá sản phẩm này rồi.</div>
                <div class="modal-footer">
                    <button
                        class="btn btn-primary"
                        @click="showReviewedModal = false"
                    >
                        OK
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: SỬA REVIEW -->
    <div
        v-if="showEditModal"
        class="modal fade show d-block"
        style="background: rgba(0, 0, 0, 0.5)"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Sửa đánh giá</h5>
                    <button
                        class="btn-close"
                        @click="showEditModal = false"
                    ></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex gap-1 mb-3">
                        <span
                            v-for="n in 5"
                            :key="n"
                            class="star selectable"
                            :class="{ active: n <= editReview.rating }"
                            @click="editReview.rating = n"
                            >★</span
                        >
                    </div>
                    <textarea
                        v-model="editReview.content"
                        class="form-control"
                        rows="3"
                    ></textarea>
                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-secondary"
                        @click="showEditModal = false"
                    >
                        Hủy
                    </button>
                    <button class="btn btn-primary" @click="submitEditReview">
                        Lưu
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: XÓA REVIEW -->
    <div
        v-if="showDeleteModal"
        class="modal fade show d-block"
        style="background: rgba(0, 0, 0, 0.5)"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Xác nhận</h5>
                    <button
                        class="btn-close"
                        @click="showDeleteModal = false"
                    ></button>
                </div>
                <div class="modal-body">
                    Bạn có chắc muốn xóa bình luận này không?
                </div>
                <div class="modal-footer">
                    <button
                        class="btn btn-secondary"
                        @click="showDeleteModal = false"
                    >
                        Hủy
                    </button>
                    <button class="btn btn-danger" @click="confirmDeleteReview">
                        Xóa
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.btn-color {
    width: 45px;
    height: 45px;
    border-radius: 50%;
    border: 3px solid #ccc;
    margin-right: 8px;
}

.color_active {
    border: 3px solid #0d6efd !important;
}

.btn-custom {
    background: transparent;
    border: none;
    font-size: 20px;
}

.star {
    color: #ccc;
    font-size: 22px;
}

.star.active {
    color: #ffc107;
}

.star.selectable {
    cursor: pointer;
}

div.descriptionProduct p {
    line-height: 2;
}

/* div.descriptionProduct {
    border: 1px solid #ccc;
    padding: 40px;
    border-radius: 5px;
} */

/* CSS Mới cho layout ảnh */
.thumbnail-scroll {
    max-height: 450px;
    overflow-y: auto;
    padding-right: 4px;
}

.thumbnail-scroll::-webkit-scrollbar {
    width: 0px;
}

.thumbnail-scroll::-webkit-scrollbar-thumb {
    background-color: #ccc;
    border-radius: 4px;
}

/* .main-img-container {
    background-color: #fff;
    min-height: 400px;
} */
.review-item {
    padding: 16px 0;
    border-bottom: 1px solid #eee;
}

.review-user {
    font-weight: 600;
    font-size: 15px;
    margin-bottom: 4px;
}

.review-stars .star {
    font-size: 16px;
    color: #ddd;
    margin-right: 2px;
}

.review-stars .star.active {
    color: #ffc107;
    /* màu vàng đẹp */
}

.review-content {
    margin: 6px 0;
    font-size: 14px;
    line-height: 1.5;
}

.review-date {
    color: #7a7a7a;
    font-size: 13px;
}

.btn-delete-review {
    margin-top: 8px;
}
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.35s ease-in-out;
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}

.fade-slide-enter-to,
.fade-slide-leave-from {
    opacity: 1;
    transform: translateY(0);
}
.review-divider {
    border-top: 1px solid #000000;
    margin-bottom: 12px;
}
.review-menu {
    position: relative;
    cursor: pointer;
}

.review-menu i {
    font-size: 18px;
    color: #666;
}

.review-dropdown {
    position: absolute;
    right: 0;
    top: 22px;
    background: #fff;
    border: 1px solid #eee;
    border-radius: 6px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
    width: 120px;
    z-index: 10;
}

.review-dropdown div {
    padding: 8px 12px;
    font-size: 14px;
}

.review-dropdown div:hover {
    background: #f5f5f5;
}
</style>
