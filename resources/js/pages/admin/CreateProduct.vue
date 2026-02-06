<script setup>
import { reactive, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../../axios';
import CategoryFormItem from '../../components/admin/CategoryFormItem.vue';


import Quill from 'quill'
import 'quill/dist/quill.snow.css'

let quill = null
const editor = ref(null)
onMounted(() => {
    quill = new Quill(editor.value, {
        theme: 'snow',
        placeholder: 'Nhập chi tiết sản phẩm...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link'],
            ],
        },
    })
})

const router = useRouter();
const categories = reactive({ items: [] });
const error = ref({});

const product = reactive({
    nameProduct: '',
    price: 0,
    description: '',
    category_id: null,
    variants: [
        {
            color: '#000000',
            images: [],
            imagePreviews: [],
            sizes: [{ size: '', stock: 0, price: 0 }],
        },
    ],
});

const handleFetchCategories = async () => {
    try {
        const res = await api.get('/categories');
        if (res.status === 200) {
            categories.items = res.data;
        }
    } catch (e) {
        console.error(e);
    }
};

onMounted(() => {
    handleFetchCategories();
});

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

    if (product.variants.length === 0) {
        alert('Cần ít nhất 1 biến thể sản phẩm.');
        isValid = false;
    } else {
        const seenColors = new Set();

        product.variants.forEach((v, vIndex) => {
            if (seenColors.has(v.color)) {
                error.value[`variantColor_${vIndex}`] = 'Màu sắc này đã bị trùng lặp.';
                isValid = false;
            } else {
                seenColors.add(v.color);
            }

            if (v.images.length === 0) {
                error.value[`variantImage_${vIndex}`] = 'Cần ít nhất 1 hình ảnh.';
                isValid = false;
            }

            const seenSizes = new Set();
            if (v.sizes.length === 0) {
                isValid = false;
            }

            v.sizes.forEach((s, sIndex) => {
                if (!s.size || s.size.trim() === '') {
                    error.value[`variantSize_${vIndex}_${sIndex}`] = 'Nhập tên size.';
                    isValid = false;
                } else {
                    const sizeName = s.size.trim().toUpperCase();
                    if (seenSizes.has(sizeName)) {
                        error.value[`variantSize_${vIndex}_${sIndex}`] = 'Size bị trùng.';
                        isValid = false;
                    } else {
                        seenSizes.add(sizeName);
                    }
                }

                if (s.stock === '' || s.stock < 0) {
                    error.value[`variantStock_${vIndex}_${sIndex}`] = 'SL không hợp lệ.';
                    isValid = false;
                }

                if (s.price < 0) {
                    error.value[`variantPrice_${vIndex}_${sIndex}`] = 'Giá không âm.';
                    isValid = false;
                }
            });
        });
    }

    return isValid;
};

const handleVariantImageUpload = (event, vIndex) => {
    const files = Array.from(event.target.files);
    if(files.length === 0) return;

    const newPreviews = files.map(file => ({
        url: URL.createObjectURL(file),
        file: file
    }));

    product.variants[vIndex].images.push(...files);
    product.variants[vIndex].imagePreviews.push(...newPreviews);
    event.target.value = '';
};

const removeVariantImage = (vIndex, imgIndex) => {
    URL.revokeObjectURL(product.variants[vIndex].imagePreviews[imgIndex].url);
    product.variants[vIndex].imagePreviews.splice(imgIndex, 1);
    product.variants[vIndex].images.splice(imgIndex, 1);
};

const setMainImage = (vIndex, imgIndex) => {
    const variant = product.variants[vIndex];
    const itemToMove = variant.imagePreviews.splice(imgIndex, 1)[0];
    variant.imagePreviews.unshift(itemToMove);
    const fileToMove = variant.images.splice(imgIndex, 1)[0];
    variant.images.unshift(fileToMove);
};

const handleCategorySelected = (id) => {
    product.category_id = id;
};

const handleAddProduct = async () => {
    if (!validateForm()) {
        alert('Vui lòng kiểm tra lại các trường báo lỗi.');
        return;
    }
    product.description = quill.root.innerHTML;

    const formData = new FormData();
    formData.append('name', product.nameProduct);
    formData.append('price', product.price);
    formData.append('description', product.description);
    formData.append('category_id', product.category_id);

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

    try {
        const response = await api.post(`/products`, formData, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });

        if (response.data.status === true) {
            alert(response.data.message);
            router.push('/admin/products');
        } else {
            alert('Lỗi: ' + response.data.message);
        }
    } catch (err) {
        console.error(err);
        if (err.response && err.response.data && err.response.data.errors) {
            error.value = err.response.data.errors;
        } else {
            alert('Có lỗi xảy ra khi thêm sản phẩm');
        }
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
                            <input v-model="product.nameProduct" type="text" class="form-control" :class="{'is-invalid': error.nameProduct}" placeholder="Ví dụ: Áo thun Polo..." />
                            <div class="invalid-feedback">{{ error.nameProduct }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Giá niêm yết (VNĐ) <span class="text-danger">*</span></label>
                            <input v-model="product.price" type="number" class="form-control" :class="{'is-invalid': error.price}" placeholder="0" />
                            <div class="invalid-feedback">{{ error.price }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Danh mục <span class="text-danger">*</span></label>
                            <div class="border rounded p-2 bg-light category-list" :class="{'border-danger': error.category_id}">
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
                            <!-- <textarea v-model="product.description" class="form-control" rows="5" placeholder="Nhập mô tả sản phẩm..."></textarea> -->
                            <div ref="editor" class="bg-white border rounded"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div v-for="(variant, vIndex) in product.variants" :key="vIndex" class="card shadow-sm border-0 mb-4 position-relative overflow-hidden">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-pill">#{{ vIndex + 1 }}</span>
                            <span class="fw-bold text-dark">Màu sắc:</span>
                            <input type="color" v-model="variant.color" class="form-control form-control-color border-0 p-0" title="Chọn màu">
                            <small v-if="error[`variantColor_${vIndex}`]" class="text-danger fw-bold ms-2">
                                <i class="bi bi-exclamation-circle"></i> {{ error[`variantColor_${vIndex}`] }}
                            </small>
                        </div>
                        <button v-if="product.variants.length > 1" type="button" class="btn btn-close" @click="product.variants.splice(vIndex, 1)"></button>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-medium mb-1">Hình ảnh <span class="text-danger">*</span></label>
                                <div class="upload-zone mb-3">
                                    <label class="btn btn-outline-primary btn-sm w-100 border-dashed py-3">
                                        <i class="bi bi-cloud-arrow-up fs-5 d-block"></i>
                                        <span>Tải ảnh lên (Chọn nhiều ảnh)</span>
                                        <input type="file" multiple class="d-none" @change="handleVariantImageUpload($event, vIndex)" accept="image/*">
                                    </label>
                                </div>
                                <div v-if="error[`variantImage_${vIndex}`]" class="text-danger small mb-2">
                                    <i class="bi bi-exclamation-circle"></i> {{ error[`variantImage_${vIndex}`] }}
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
                                                <th style="width: 30%">Size <span class="text-danger">*</span></th>
                                                <th style="width: 30%">Số lượng <span class="text-danger">*</span></th>
                                                <th style="width: 30%">Giá riêng</th>
                                                <th style="width: 10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(size, sIndex) in variant.sizes" :key="sIndex">
                                                <td>
                                                    <input v-model="size.size" type="text" class="form-control form-control-sm text-center"
                                                        :class="{'is-invalid': error[`variantSize_${vIndex}_${sIndex}`]}" placeholder="S, M..." />
                                                    <div class="invalid-feedback text-xs text-start ps-2">{{ error[`variantSize_${vIndex}_${sIndex}`] }}</div>
                                                </td>
                                                <td>
                                                    <input v-model="size.stock" type="number" class="form-control form-control-sm text-center"
                                                        :class="{'is-invalid': error[`variantStock_${vIndex}_${sIndex}`]}" />
                                                    <div class="invalid-feedback text-xs text-start ps-2">{{ error[`variantStock_${vIndex}_${sIndex}`] }}</div>
                                                </td>
                                                <td>
                                                    <input v-model="size.price" type="number" class="form-control form-control-sm text-center"
                                                        :class="{'is-invalid': error[`variantPrice_${vIndex}_${sIndex}`]}" placeholder="Mặc định" />
                                                    <div class="invalid-feedback text-xs text-start ps-2">{{ error[`variantPrice_${vIndex}_${sIndex}`] }}</div>
                                                </td>
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
.category-list {
    max-height: 200px;
    overflow-y: auto;
}
.border-dashed {
    border-style: dashed !important;
}
.image-card {
    width: 100px;
    height: 100px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.2s;
    border-radius: 4px;
}
.image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.image-card.is-main {
    border: 3px solid #ffc107 !important;
}
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
}
.image-card:hover .image-actions {
    opacity: 1;
}
.text-xs {
    font-size: 0.75rem;
}
</style>
