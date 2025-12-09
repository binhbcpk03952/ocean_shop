<script setup>
import { ref, onMounted, watch, reactive, computed, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Toast } from "bootstrap";
import api from '../../axios';
import { emitter } from '../../stores/eventBus';

const route = useRoute();
const router = useRouter();
const { auth } = inject('auth');
const productId = ref(route.params.id);

// Modal đăng nhập
const showLoginModal = ref(false);

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
    ⭐⭐  BÌNH LUẬN + ĐÁNH GIÁ (GIỮ NGUYÊN)
--------------------------------------------------- */
const reviews = ref({});
const reviewForm = reactive({ rating: 0, content: "" });
const myReview = ref(null);
const avgRating = computed(() => {
    if (!Array.isArray(reviews.value) || !reviews.value.length) return 0;
    const sum = reviews.value.reduce((t, r) => t + (r.rating ?? 0), 0);
    return (sum / reviews.value.length).toFixed(1);
});

const fetchReviews = async () => {
    try {
        const res = await api.get(`/reviews/${productId.value}`);
        if (res.status === 200) {
            reviews.value = res.data
        }
    } catch (err) { console.error("Lỗi tải review:", err); reviews.value = []; }
};

const handleSubmitReview = async () => {
    if (!auth?.loggedIn) return showLoginModal.value = true;
    if (!reviewForm.rating) return alert("Vui lòng chọn số sao!");
    try {
        if (myReview.value) {
            await api.put(`reviews/${myReview.value.review_id}`, reviewForm);
        } else {
            await api.post("reviews", { product_id: productId.value, ...reviewForm });
        }
        reviewForm.content = "";
        fetchReviews();
    } catch (err) { console.error("Lỗi gửi review:", err); }
};

const deleteReview = async (id) => {
    if (!confirm("Bạn có chắc muốn xóa?")) return;
    try {
        await api.delete(`reviews/${id}`);
        fetchReviews();
        myReview.value = null; reviewForm.rating = 0; reviewForm.content = "";
    } catch (err) { console.error("Lỗi xóa:", err); }
};

/* --------------------------------------------------
    🎨  SẢN PHẨM (MÀU / SIZE)
--------------------------------------------------- */
const selectedColor = ref(null);
const selectedSize = ref(null);

const uniqueVariants = computed(() => {
    if (!productDetail.value) return [];
    const seen = new Set();
    return productDetail.value.variant.filter(v => {
        const isDup = seen.has(v.color);
        seen.add(v.color);
        return !isDup;
    });
});

watch(uniqueVariants, (v) => {
    if (v.length > 0 && !selectedColor.value) selectedColor.value = v[0].color;
}, { immediate: true });

const uniqueSizes = computed(() => {
    if (!productDetail.value || !selectedColor.value) return [];
    return productDetail.value.variant.filter(v => v.color === selectedColor.value);
});

watch(selectedColor, () => {
    if (uniqueSizes.value.length > 0) selectedSize.value = uniqueSizes.value[0].size;
}, { immediate: true });

/* --------------------------------------------------
    🎨  ẢNH (LOGIC MỚI)
--------------------------------------------------- */

// Lọc ảnh theo màu sắc đang chọn
const imagesBySelectedColor = computed(() => {
    if (!productDetail.value || !selectedColor.value) return [];

    // 1. Lấy danh sách ID biến thể của màu đang chọn
    const variantIdsOfColor = productDetail.value.variant
        .filter(v => v.color === selectedColor.value)
        .map(v => v.variant_id);

    // 2. Lọc ảnh có variant_id nằm trong danh sách trên
    const rawImages = productDetail.value.image.filter(img =>
        variantIdsOfColor.includes(img.variant_id)
    );

    // 3. Khử trùng lặp URL (nếu nhiều size dùng chung ảnh)
    const seen = new Set();
    return rawImages.filter(img => {
        const isDup = seen.has(img.image_url);
        seen.add(img.image_url);
        return !isDup;
    });
});

const currentDisplayImage = ref(null);

// Tự động đổi ảnh hiển thị khi list ảnh thay đổi (do đổi màu)
watch(imagesBySelectedColor, (newImages) => {
    if (newImages && newImages.length > 0) {
        // Ưu tiên ảnh is_main = 1, nếu không thì lấy cái đầu tiên
        const main = newImages.find(i => i.is_main === 1) || newImages[0];
        currentDisplayImage.value = main.image_url;
    }
}, { immediate: true });

/* --------------------------------------------------
    🛒  CART
--------------------------------------------------- */
const cartValue = reactive({ count: 1, variant_id: null });
const selectedVariant = computed(() => {
    if (!productDetail.value) return null;
    return productDetail.value.variant.find(v => v.color === selectedColor.value && v.size === selectedSize.value);
});
watch(selectedVariant, (newV) => { cartValue.variant_id = newV ? newV.variant_id : null; }, { immediate: true });

const showToast = (message, type = "success") => {
    const toastEl = document.getElementById("cartToast");
    toastEl.classList.remove("text-bg-success", "text-bg-danger");
    toastEl.classList.add(`text-bg-${type}`);
    toastEl.querySelector(".toast-body").innerText = message;
    new Toast(toastEl).show();
};

const handleAddToCart = async () => {
    if (!cartValue.variant_id) return showToast("Vui lòng chọn biến thể!", "danger");
    if (!auth?.loggedIn) return showLoginModal.value = true;
    try {
        const res = await api.post("carts", cartValue);
        if (res.status === 200) { showToast("Thêm vào giỏ hàng thành công!"); cartValue.count = 1; }
        emitter.emit('update_stock-cart')
    } catch (err) { showToast("Có lỗi xảy ra!", "danger"); }
};

/* --------------------------------------------------
    🚪 LOGIN MODAL & MOUNT
--------------------------------------------------- */
const goToLogin = () => { showLoginModal.value = false; router.push("/login?back=products/" + productId.value); };
const closeModal = () => showLoginModal.value = false;

onMounted(() => { handleFetchProductDetailById(productId.value); fetchReviews(); });
watch(() => route.params.id, (id) => { handleFetchProductDetailById(id); fetchReviews(); });
const formatDate = (dateString) => {
    const d = new Date(dateString);
    return d.toLocaleString("vi-VN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit"
    });
};

</script>

<template>
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999">
        <div id="cartToast" class="toast text-bg-success border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">Thông báo</div><button type="button"
                    class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <div v-if="showLoginModal" class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-header">
                    <h5 class="modal-title">Thông báo</h5><button type="button" class="btn-close"
                        @click="closeModal"></button>
                </div>
                <div class="modal-body text-center">Vui lòng đăng nhập để thực hiện hành động này.</div>
                <div class="modal-footer"><button class="btn btn-secondary" @click="closeModal">Hủy</button><button
                        class="btn btn-primary" @click="goToLogin">OK</button></div>
            </div>
        </div>
    </div>

    <div class="container" v-if="productDetail">
        <div class="row">

            <div class="col-lg-6 p-5">
                <div class="d-flex gap-3 h-100">

                    <div class="d-flex flex-column gap-2 thumbnail-scroll">
                        <img v-for="img in imagesBySelectedColor" :key="img.image_id"
                            :src="`../../../../storage/${img.image_url}`" @click="currentDisplayImage = img.image_url"
                            class="border" :class="{ 'border-main': currentDisplayImage === img.image_url }"
                            style="width: 70px; height: auto; object-fit: cover; cursor: pointer;">
                    </div>

                    <div class="flex-grow-1 d-flex align-items-center justify-content-center main-img-container">
                        <img v-if="currentDisplayImage" :src="`../../../../storage/${currentDisplayImage}`"
                            class="img-fluid rounded-1" style="max-height: 450px; object-fit: contain;">
                    </div>

                </div>
            </div>

            <div class="col-lg-6 p-5">
                <div class="fs-4 fw-bold text-danger">{{ productDetail.price.toLocaleString() }}₫</div>
                <div class="fs-3 fw-semibold mb-3">{{ productDetail.name }}</div>

                <div class="d-flex align-items-center gap-2 mb-3" v-if="reviews">
                    <span class="star" v-for="n in 5" :class="{ active: n <= Math.round(avgRating) }">★</span>
                    <span class="text-muted">({{ reviews.total_reviews }})</span>
                </div>

                <div class="mt-3">
                    <span>Màu sắc: </span>
                    <div class="d-flex mt-2">
                        <button v-for="v in uniqueVariants" :key="v.variant_id" class="btn-color"
                            :class="{ color_active: v.color === selectedColor }" :style="{ background: v.color }"
                            @click="selectedColor = v.color">
                        </button>
                    </div>
                </div>

                <div class="mt-3">
                    <span>Kích thước: </span>
                    <div class="d-flex mt-2">
                        <button v-for="v in uniqueSizes" :key="v.variant_id" class="btn-color"
                            style="background:transparent;" :class="{ color_active: v.size === selectedSize }"
                            @click="selectedSize = v.size">
                            {{ v.size }}
                        </button>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-4">
                        <div class="border rounded-4 d-flex justify-content-between px-3 py-2">
                            <button class="btn-custom" @click="cartValue.count--" :disabled="cartValue.count <= 1"><i
                                    class="bi bi-dash"></i></button>
                            <span>{{ cartValue.count }}</span>
                            <button class="btn-custom" @click="cartValue.count++"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                    <div class="col-8">
                        <button class="btn btn-primary w-100 py-2 rounded-4" @click="handleAddToCart">Thêm vào giỏ
                            hàng</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mt-5 descriptionProduct">
            <h3 class="mb-4">Mô tả sản phẩm</h3>
            <p>{{ productDetail.description }}</p>
        </div>

        <div class="row mt-5">
            <div class="col-lg-8">
                <!-- <h3 class="mb-4">Đánh giá sản phẩm</h3>
                <div class="card p-3 mb-4">
                    <h5>{{ myReview ? "Sửa đánh giá của bạn" : "Viết đánh giá" }}</h5>
                    <div class="d-flex gap-1 mt-2">
                        <span v-for="n in 5" :key="n" class="star selectable"
                            :class="{ active: n <= reviewForm.rating }" @click="reviewForm.rating = n">★</span>
                    </div>
                    <textarea v-model="reviewForm.content" class="form-control mt-3" rows="3"
                        placeholder="Chia sẻ cảm nhận..."></textarea>
                    <button class="btn btn-primary mt-3 w-100"
                        @click="handleSubmitReview"
                    >
                        {{ myReview ? "Cập nhật đánh giá" : "Gửi đánh giá" }}
                    </button>

                </div> -->
                <div class="card p-3 mb-5">
                    <h5 class="mb-3">Tất cả đánh giá</h5>
                    <div v-if="!reviews || reviews.reviews?.length === 0" class="text-muted">
                        Chưa có đánh giá nào.
                    </div>
                    <div v-for="r in reviews.reviews" :key="r.review_id" class="review-item">
                        <div class="review-user">{{ r.user.name }}</div>
                        <div class="review-stars">
                            <span v-for="n in 5" :key="n" class="star" :class="{ active: n <= r.rating }">★</span>
                        </div>
                        <div class="review-content">{{ r.content }}</div>
                        <div class="review-date">{{ formatDate(r.created_at) }}</div>
                        <div v-if="auth.loggedIn && r.user_id === auth.user.id">
                            <button class="btn btn-sm btn-outline-danger btn-delete-review"
                                @click="deleteReview(r.review_id)">
                                Xóa
                            </button>
                        </div>
                    </div>
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

div.descriptionProduct {
    border: 1px solid #ccc;
    padding: 40px;
    border-radius: 5px;
}

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
</style>
