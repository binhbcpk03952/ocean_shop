<script setup>
import { ref, onMounted, watch, reactive, computed, inject } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { Toast } from "bootstrap";
import api from '../../axios';

const route = useRoute();
const router = useRouter();
const { auth } = inject('auth');

const productId = ref(route.params.id);

/* --------------------------- LOGIN MODAL --------------------------- */
const showLoginModal = ref(false);
const goToLogin = () => {
    showLoginModal.value = false;
    router.push("/login?back=products/" + productId.value);
};
const closeModal = () => showLoginModal.value = false;

/* --------------------------- PRODUCT DETAIL ------------------------ */
const productDetail = ref(null);

const handleFetchProductDetailById = async (id) => {
    try {
        const res = await api.get(`products/${id}`);
        productDetail.value = res.data;
    } catch (err) {
        console.error("Lỗi khi tải chi tiết:", err);
    }
};

/* --------------------------- REVIEW + RATING ------------------------ */
const reviews = ref([]);
const myReview = ref(null);

const reviewForm = reactive({
    rating: 0,
    content: ""
});

// ⭐ tính trung bình sao
const avgRating = computed(() => {
    if (!reviews.value.length) return 0;
    const sum = reviews.value.reduce((t, r) => t + r.rating, 0);
    return (sum / reviews.value.length).toFixed(1);
});

// ⭐ Lấy danh sách bình luận
const fetchReviews = async () => {
    try {
        const res = await api.get(`products/${productId.value}/reviews`);
        reviews.value = res.data;

        if (auth.loggedIn) {
            myReview.value = reviews.value.find(
                r => r.user_id === auth.user.user_id
            ) || null;

            if (myReview.value) {
                reviewForm.rating = myReview.value.rating;
                reviewForm.content = myReview.value.content;
            }
        }
    } catch (err) {
        console.error("Lỗi tải review:", err);
    }
};

// ⭐ Gửi hoặc cập nhật review
const handleSubmitReview = async () => {
    if (!auth.loggedIn) return showLoginModal.value = true;
    if (!reviewForm.rating) return alert("Vui lòng chọn số sao!");

    try {
        if (myReview.value) {
            await api.put(`reviews/${myReview.value.review_id}`, reviewForm);
        } else {
            await api.post("reviews", {
                product_id: productId.value,
                rating: reviewForm.rating,
                content: reviewForm.content
            });
        }

        reviewForm.content = "";
        fetchReviews();
    } catch (err) {
        if (err.response && err.response.status === 400) {
            alert("Bạn đã đánh giá sản phẩm này rồi.");
        }
        console.error("Lỗi gửi review:", err);
    }
};

// ⭐ Xóa review (hỗ trợ admin)
const deleteReview = async (id) => {
    if (!confirm("Bạn có chắc muốn xóa bình luận này không?")) return;

    try {
        await api.delete(`reviews/${id}`);
        fetchReviews();
        myReview.value = null;
        reviewForm.rating = 0;
        reviewForm.content = "";
    } catch (err) {
        console.error("Lỗi xóa review:", err);
    }
};

/* --------------------------- VARIANT COLOR SIZE --------------------------- */

const selectedColor = ref(null);
const selectedSize = ref(null);

const uniqueVariants = computed(() => {
    if (!productDetail.value) return [];
    const seen = new Set();
    return productDetail.value.variant.filter(v => {
        const dup = seen.has(v.color);
        seen.add(v.color);
        return !dup;
    });
});

watch(uniqueVariants, (v) => {
    if (v.length && !selectedColor.value) selectedColor.value = v[0].color;
}, { immediate: true });

const uniqueSizes = computed(() => {
    if (!productDetail.value || !selectedColor.value) return [];
    return productDetail.value.variant.filter(v => v.color === selectedColor.value);
});

watch(selectedColor, () => {
    if (uniqueSizes.value.length) selectedSize.value = uniqueSizes.value[0].size;
}, { immediate: true });

/* ------------------------------ IMAGE SYSTEM ------------------------------ */

const uniquedImages = computed(() => {
    if (!productDetail.value) return [];
    const seen = new Set();
    return productDetail.value.image.filter(img => {
        const dup = seen.has(img.image_url);
        seen.add(img.image_url);
        return !dup;
    });
});

const currentDisplayImage = ref(null);

const mainImage = computed(() =>
    productDetail.value?.image.find(i => i.is_main === 1)
    || productDetail.value?.image[0]
);

watch(mainImage, (img) => {
    if (img) currentDisplayImage.value = img.image_url;
}, { immediate: true });

/* ------------------------------ CART ------------------------------ */

const cartValue = reactive({
    count: 1,
    variant_id: null,
});

const selectedVariant = computed(() => {
    if (!productDetail.value) return null;
    return productDetail.value.variant.find(v =>
        v.color === selectedColor.value &&
        v.size === selectedSize.value
    );
});

watch(selectedVariant, (v) => {
    cartValue.variant_id = v ? v.variant_id : null;
}, { immediate: true });

const showToast = (message, type = "success") => {
    const el = document.getElementById("cartToast");
    el.classList.remove("text-bg-success", "text-bg-danger");
    el.classList.add(`text-bg-${type}`);
    el.querySelector(".toast-body").innerText = message;
    new Toast(el).show();
};

const handleAddToCart = async () => {
    if (!cartValue.variant_id)
        return showToast("Vui lòng chọn biến thể!", "danger");
    if (!auth.loggedIn)
        return showLoginModal.value = true;

    try {
        await api.post("carts", cartValue);
        showToast("Thêm vào giỏ hàng thành công!");
        cartValue.count = 1;
    } catch (err) {
        showToast("Có lỗi xảy ra!", "danger");
    }
};

/* ------------------------------ ON MOUNT ------------------------------ */

onMounted(() => {
    handleFetchProductDetailById(productId.value);
    fetchReviews();
});

watch(() => route.params.id, (id) => {
    handleFetchProductDetailById(id);
    fetchReviews();
});
</script>

<template>

    <!-- TOAST -->
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div id="cartToast" class="toast text-bg-success border-0">
            <div class="d-flex">
                <div class="toast-body">Thông báo</div>
                <button class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    <!-- LOGIN MODAL -->
    <div v-if="showLoginModal" class="modal fade show d-block" style="background:rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-2">
                <div class="modal-header">
                    <h5 class="modal-title">Thông báo</h5>
                    <button class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body text-center">
                    Vui lòng đăng nhập để tiếp tục.
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="closeModal">Hủy</button>
                    <button class="btn btn-primary" @click="goToLogin">OK</button>
                </div>
            </div>
        </div>
    </div>

    <!-- PRODUCT DETAIL -->
    <div class="container" v-if="productDetail">

        <!-- PRODUCT INFO -->
        <div class="row">

            <!-- IMAGE -->
            <div class="col-lg-6 p-5">
                <div class="main-img border rounded mb-2 p-2 text-center">
                    <img :src="`../../../../storage/${currentDisplayImage}`"
                         class="img-fluid rounded-1"
                         style="max-height:350px;">
                </div>

                <div class="thumbnail-list d-flex gap-2">
                    <img v-for="img in uniquedImages" :key="img.image_id"
                         :src="`../../../../storage/${img.image_url}`"
                         class="img-thumbnail"
                         style="width:70px;height:70px;cursor:pointer"
                         @click="currentDisplayImage = img.image_url">
                </div>
            </div>

            <!-- INFO -->
            <div class="col-lg-6 p-5">
                <div class="fs-4 fw-bold text-danger">
                    {{ productDetail.price.toLocaleString() }}₫
                </div>

                <div class="fs-3 fw-semibold mb-3">
                    {{ productDetail.name }}
                </div>

                <!-- ⭐ AVG RATING -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <span v-for="n in 5" :key="n" class="star" :class="{active:n<=Math.round(avgRating)}">★</span>
                    <span class="text-muted">({{ avgRating }})</span>
                </div>

                <!-- COLOR -->
                <div class="mt-3">
                    <span>Màu sắc:</span>
                    <div class="d-flex mt-2">
                        <button v-for="v in uniqueVariants" :key="v.variant_id"
                                class="btn-color"
                                :class="{color_active:v.color===selectedColor}"
                                :style="{background:v.color}"
                                @click="selectedColor=v.color">
                        </button>
                    </div>
                </div>

                <!-- SIZE -->
                <div class="mt-3">
                    <span>Kích thước:</span>
                    <div class="d-flex mt-2">
                        <button v-for="v in uniqueSizes" :key="v.variant_id"
                                class="btn-color"
                                style="background:transparent"
                                :class="{color_active:v.size===selectedSize}"
                                @click="selectedSize=v.size">
                            {{ v.size }}
                        </button>
                    </div>
                </div>

                <!-- ADD TO CART -->
                <div class="row mt-4">
                    <div class="col-4">
                        <div class="border rounded-4 d-flex justify-content-between px-3 py-2">
                            <button class="btn-custom" @click="cartValue.count--"
                                    :disabled="cartValue.count<=1">
                                <i class="bi bi-dash"></i>
                            </button>
                            <span>{{ cartValue.count }}</span>
                            <button class="btn-custom" @click="cartValue.count++">
                                <i class="bi bi-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="col-8">
                        <button class="btn btn-primary w-100 py-2 rounded-4"
                                @click="handleAddToCart">
                            Thêm vào giỏ hàng
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <!-- ---------------------- COMMENT SECTION ---------------------- -->

        <div class="row mt-5">
            <div class="col-lg-8 mx-auto">

                <h3 class="mb-4">Bình luận</h3>

                <!-- FORM REVIEW -->
                <div class="card p-3 mb-4">
                    <h5>{{ myReview ? "Sửa đánh giá của bạn" : "Viết bình luận" }}</h5>

                    <!-- rating -->
                    <div class="d-flex gap-1 mt-2">
                        <span v-for="n in 5" :key="n"
                              class="star selectable"
                              :class="{active:n<=reviewForm.rating}"
                              @click="reviewForm.rating=n">★</span>
                    </div>

                    <textarea v-model="reviewForm.content"
                              class="form-control mt-3"
                              rows="3"
                              placeholder="Chia sẻ cảm nhận của bạn..."></textarea>

                    <button class="btn btn-primary mt-3 w-100"
                            @click="handleSubmitReview">
                        {{ myReview ? "Cập nhật đánh giá" : "Gửi đánh giá" }}
                    </button>
                </div>

                <!-- REVIEW LIST -->
                <div class="card p-3">
                    <h5 class="mb-3">Tất cả bình luận</h5>

                    <div v-if="!reviews.length" class="text-muted">
                        Chưa có bình luận nào.
                    </div>

                    <div v-for="r in reviews" :key="r.review_id" class="border-bottom py-3">
                        <div class="fw-semibold">{{ r.user.name }}</div>

                        <div class="text-warning small">
                            <span v-for="n in 5" :key="n"
                                  class="star"
                                  :class="{active:n<=r.rating}">
                                ★
                            </span>
                        </div>

                        <div>{{ r.content }}</div>
                        <div class="text-muted small">{{ r.created_at }}</div>

                        <!-- nút xóa (chủ bình luận hoặc admin) -->
                        <div v-if="auth.loggedIn && (r.user_id === auth.user.user_id || auth.user.role === 'admin')"
                             class="mt-2">
                            <button class="btn btn-sm btn-outline-danger"
                                    @click="deleteReview(r.review_id)">
                                Xóa bình luận
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
</style>
