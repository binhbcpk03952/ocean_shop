<script setup>
import { reactive, onMounted, ref, toRaw } from 'vue';
import api from '../../axios'; // Đảm bảo đường dẫn đúng
import { useRouter } from 'vue-router';
import CategoryFormItem from '../../components/admin/CategoryFormItem.vue';

const router = useRouter();
const categories = reactive({ items: [] });
const error = ref({});

// 1. Fetch Danh mục
const handleFetchCategories = async () => {
    try {
        const res = await api.get('/categories');
        if (res.status === 200) {
            categories.items = res.data;
        }
    } catch (e) {
        console.error('Lỗi lấy danh mục:', e);
    }
};

onMounted(() => {
    handleFetchCategories();
});

// 2. Validate Form
const validateForm = () => {
    error.value = {};
    let isValid = true;

    if (!product.nameProduct) {
        error.value.nameProduct = 'Tên sản phẩm là bắt buộc.';
        isValid = false;
    }
    if (!product.price || product.price <= 0) {
        error.value.price = 'Giá sản phẩm phải lớn hơn 0.';
        isValid = false;
    }
    if (!product.category_id) {
        error.value.category_id = 'Vui lòng chọn danh mục sản phẩm.';
        isValid = false;
    }

    // Kiểm tra biến thể: Phải có ít nhất 1 biến thể và mỗi biến thể phải có ít nhất 1 ảnh
    if (product.variants.length === 0) {
        alert('Cần ít nhất 1 biến thể sản phẩm.');
        isValid = false;
    } else {
        product.variants.forEach((v, index) => {
            if (v.images.length === 0) {
                alert(`Biến thể màu ${v.color} chưa có hình ảnh nào.`);
                isValid = false;
            }
        });
    }

    return isValid;
}

// 3. Khởi tạo Object Product
const product = reactive({
    nameProduct: '',
    price: 0,
    description: '',
    category_id: null,
    // Không còn mảng images (chung) nữa
    variants: [
        {
            color: '#000000',
            // File ảnh thực tế để gửi đi
            images: [],
            // URL blob để preview
            imagePreviews: [],
            sizes: [
                { size: '', stock: 0, price: 0 }
            ],
        },
    ],
});

// 4. Xử lý Upload ảnh trong Biến thể
const handleVariantImageUpload = (event, vIndex) => {
    const files = Array.from(event.target.files);
    if(files.length === 0) return;

    // Nối thêm file vào mảng hiện tại thay vì ghi đè
    const newPreviews = files.map(file => ({
        url: URL.createObjectURL(file),
        file: file
    }));

    // Cập nhật vào state
    product.variants[vIndex].images.push(...files);
    product.variants[vIndex].imagePreviews.push(...newPreviews);

    // Reset input file để có thể chọn lại cùng 1 file nếu lỡ xóa
    event.target.value = '';
}

// 5. Xóa ảnh trong biến thể
const removeVariantImage = (vIndex, imgIndex) => {
    URL.revokeObjectURL(product.variants[vIndex].imagePreviews[imgIndex].url); // Giải phóng bộ nhớ
    product.variants[vIndex].imagePreviews.splice(imgIndex, 1);
    product.variants[vIndex].images.splice(imgIndex, 1);
}

// 6. Đặt làm ảnh chính (Đưa lên đầu mảng)
const setMainImage = (vIndex, imgIndex) => {
    const variant = product.variants[vIndex];

    // Di chuyển trong mảng Preview
    const itemToMove = variant.imagePreviews.splice(imgIndex, 1)[0];
    variant.imagePreviews.unshift(itemToMove);

    // Di chuyển trong mảng File thực tế
    const fileToMove = variant.images.splice(imgIndex, 1)[0];
    variant.images.unshift(fileToMove);
}

const handleCategorySelected = (id) => {
    product.category_id = id;
}

// 7. Gửi dữ liệu (Submit)
const handleAddProduct = async () => {
    if (!validateForm()) return;

    const formData = new FormData();
    formData.append('name', product.nameProduct);
    formData.append('price', product.price);
    formData.append('description', product.description);
    formData.append('category_id', product.category_id);

    // Chuẩn bị JSON variants (chỉ chứa thông tin text, không chứa file)
    const variantsData = product.variants.map(variant => ({
        color: variant.color,
        sizes: variant.sizes,
    }));
    formData.append('variants', JSON.stringify(variantsData));

    product.variants.forEach((variant, index) => {
        variant.images.forEach(imageFile => {
            formData.append(`variant_images[${index}][]`, imageFile);
        });
    });

    // Gửi dữ liệu lên API
    console.log('Dữ liệu FormData đã chuẩn bị để gửi đi:');
    for (let [key, value] of formData.entries()) {
        console.log(`- ${key}:`, value);
    }




    try {
        const response = await api.post(`/products`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        }); // Bỏ header Content-Type thủ công

        if (response.data.status === true) {
            alert(response.data.message);
            router.push('/admin/products');
        } else {
            console.log(response.data.message);
            alert('Lỗi: ' + response.data.message);
        }
    } catch (err) {
        console.error('Lỗi API:', err);
        alert('Có lỗi xảy ra khi thêm sản phẩm');
    }
};
</script>

<template>
    <div class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-primary m-0">
                <i class="bi bi-plus-circle-dotted me-2"></i>Thêm sản phẩm mới
            </h3>
            <button @click="handleAddProduct" class="btn btn-primary px-4 fw-bold">
                <i class="bi bi-save me-2"></i>Lưu sản phẩm
            </button>
        </div>

        <form @submit.prevent="handleAddProduct" class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold m-0 text-uppercase text-secondary"><i class="bi bi-info-circle me-2"></i>Thông tin chung</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input v-model="product.nameProduct" type="text" class="form-control" placeholder="Ví dụ: Áo thun Polo..." />
                            <small v-if="error.nameProduct" class="text-danger">{{ error.nameProduct }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Giá niêm yết (VNĐ) <span class="text-danger">*</span></label>
                            <input v-model="product.price" type="number" class="form-control" placeholder="0" />
                            <small v-if="error.price" class="text-danger">{{ error.price }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Danh mục <span class="text-danger">*</span></label>
                            <div class="border rounded p-2 bg-light category-list">
                                <ul class="list-unstyled mb-0">
                                    <CategoryFormItem v-for="cat in categories.items" :key="cat.category_id" :cat="cat"
                                        :level="0" :current-parent-id="categories.parent"
                                        @update:parent="handleCategorySelected" />
                                </ul>
                            </div>
                            <small v-if="error.category_id" class="text-danger">{{ error.category_id }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Mô tả chi tiết</label>
                            <textarea v-model="product.description" class="form-control" rows="5" placeholder="Nhập mô tả sản phẩm..."></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div v-for="(variant, vIndex) in product.variants" :key="vIndex" class="card shadow-sm border-0 mb-4 position-relative overflow-hidden">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill">#{{ vIndex + 1 }}</span>
                            <span class="fw-bold text-dark">Biến thể màu sắc</span>
                            <input type="color" v-model="variant.color" class="form-control form-control-color border-0 p-0" title="Chọn màu">
                        </div>
                        <button v-if="product.variants.length > 1" type="button" class="btn btn-close" @click="product.variants.splice(vIndex, 1)"></button>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-medium mb-1">Hình ảnh cho màu này</label>
                                <p class="text-muted small fst-italic mb-2">
                                    Ảnh đầu tiên sẽ là <strong>ảnh đại diện</strong> cho màu sắc này. Nhấn vào ngôi sao để chọn ảnh chính.
                                </p>

                                <div class="upload-zone mb-3">
                                    <label class="btn btn-outline-primary btn-sm w-100 border-dashed py-3">
                                        <i class="bi bi-cloud-arrow-up fs-5 d-block"></i>
                                        <span>Tải ảnh lên (Chọn nhiều ảnh)</span>
                                        <input type="file" multiple class="d-none" @change="handleVariantImageUpload($event, vIndex)" accept="image/*">
                                    </label>
                                </div>

                                <div v-if="variant.imagePreviews.length" class="d-flex flex-wrap gap-3">
                                    <div v-for="(img, i) in variant.imagePreviews" :key="i" class="position-relative image-card" :class="{'is-main': i === 0}">
                                        <img :src="img.url" class="rounded border" />

                                        <span v-if="i === 0" class="position-absolute top-0 start-0 badge bg-warning text-dark m-1 shadow-sm">Ảnh chính</span>

                                        <div class="image-actions">
                                            <button v-if="i !== 0" type="button" class="btn btn-sm btn-light text-warning mb-1" @click="setMainImage(vIndex, i)" title="Đặt làm ảnh chính">
                                                <i class="bi bi-star-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger" @click="removeVariantImage(vIndex, i)" title="Xóa ảnh">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-else class="text-center py-4 bg-light rounded text-muted small">
                                    Chưa có hình ảnh nào cho biến thể này.
                                </div>
                            </div>

                            <hr class="text-muted opacity-25">

                            <div class="col-12">
                                <label class="form-label fw-medium">Cấu hình Kích thước & Tồn kho</label>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm align-middle text-center mb-0">
                                        <thead class="bg-light text-secondary">
                                            <tr>
                                                <th style="width: 30%">Size</th>
                                                <th style="width: 30%">Số lượng</th>
                                                <th style="width: 30%">Giá riêng (Optional)</th>
                                                <th style="width: 10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(size, sIndex) in variant.sizes" :key="sIndex">
                                                <td><input v-model="size.size" type="text" class="form-control form-control-sm text-center" placeholder="S, M, L..." /></td>
                                                <td><input v-model="size.stock" type="number" class="form-control form-control-sm text-center" /></td>
                                                <td><input v-model="size.price" type="number" class="form-control form-control-sm text-center" placeholder="Mặc định" /></td>
                                                <td>
                                                    <button type="button" class="btn btn-link text-danger p-0" @click="variant.sizes.splice(sIndex, 1)">
                                                        <i class="bi bi-x-circle fs-5"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-2 w-100 border-dashed"
                                    @click="variant.sizes.push({ size: '', stock: 0, price: 0 })">
                                    <i class="bi bi-plus"></i> Thêm kích thước
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-success border-dashed w-100 py-3 fw-bold"
                    @click="product.variants.push({ color: '#000000', images: [], imagePreviews: [], sizes: [{ size: '', stock: 0, price: 0 }] })">
                    <i class="bi bi-plus-circle me-2"></i>Thêm biến thể màu khác
                </button>
            </div>
        </form>
    </div>
</template>

<style scoped>
/* Custom Scrollbar cho danh mục */
.category-list {
    max-height: 200px;
    overflow-y: auto;
}

/* Style cho vùng upload */
.border-dashed {
    border-style: dashed !important;
}

/* Style cho Grid ảnh */
.image-card {
    width: 100px;
    height: 100px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
}

.image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-card.is-main {
    border: 2px solid #ffc107 !important; /* Viền vàng cho ảnh chính */
    border-radius: 6px;
}

/* Hiệu ứng hover hiện nút thao tác */
.image-actions {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.2s;
    border-radius: 4px;
}

.image-card:hover .image-actions {
    opacity: 1;
}
</style>
