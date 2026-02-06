<script setup>
import { reactive, onMounted, ref, toRaw, watch } from 'vue';
import api from '../../axios'; // Đảm bảo đường dẫn đúng
import { useRouter, useRoute } from 'vue-router'; // Import useRoute để lấy ID sản phẩm
import CategoryFormItem from '../../components/admin/CategoryFormItem.vue';


import Quill from 'quill'
import 'quill/dist/quill.snow.css'

// let quill = null
// const editor = ref(null)
// onMounted(() => {
//     quill = new Quill(editor.value, {
//         theme: 'snow',
//         placeholder: 'Nhập chi tiết sản phẩm...',
//         modules: {
//             toolbar: [
//                 [{ header: [1, 2, 3, false] }],
//                 ['bold', 'italic', 'underline'],
//                 [{ list: 'ordered' }, { list: 'bullet' }],
//                 ['link'],
//             ],
//         },
//     })
// })


const router = useRouter();
const route = useRoute(); // Dùng để lấy product_id từ URL
const product_id = route.params.id; // Giả sử route là /admin/products/edit/:id

const categories = reactive({ items: [] });
const error = ref({});
const isLoading = ref(true); // Biến để hiển thị loading state
const initialProductData = ref(null); // Để lưu trạng thái ban đầu khi fetch


// --- FETCH DỮ LIỆU BAN ĐẦU ---
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

const fetchProductData = async () => {
    // Chỉ lấy product_id khi route đã sẵn sàng
    if (!product_id) {
        isLoading.value = false;
        return;
    }

    try {
        const res = await api.get(`/products/${product_id}`);
        if (res.status !== 200) {
            throw new Error('Không thể tải dữ liệu sản phẩm.');
        }

        const fetchedProduct = res.data; // Dữ liệu JSON bạn đã cung cấp

        // --- 1. Nhóm Variants (SKU/Sizes) theo Màu Sắc ---
        const groupedVariants = fetchedProduct.variant.reduce((acc, sku) => {
            const color = sku.color;
            if (!acc[color]) {
                acc[color] = {
                    color: color,
                    sizes: [],
                    variantIds: new Set(), // Để lưu các variant_id (size) thuộc nhóm màu này
                    images: [],
                };
            }
            // Thêm size vào nhóm màu
            acc[color].sizes.push({
                id: sku.variant_id,
                size: sku.size,
                stock: sku.stock,
                price: sku.price,
                isNew: false,
            });
            acc[color].variantIds.add(sku.variant_id);
            return acc;
        }, {});

        // --- 2. Nhóm Images vào từng Color Group ---
        const imageMap = new Map(); // Dùng Map để lưu trữ và deduplicate ảnh theo image_id

        fetchedProduct.image.forEach(img => {
            // Lấy ra color ID từ variant_id đầu tiên được gán cho image đó
            const firstVariantId = img.variant_id;
            const variantSku = fetchedProduct.variant.find(v => v.variant_id === firstVariantId);
            if (!variantSku) return;

            const color = variantSku.color;
            const imageKey = img.image_url; // Dùng URL làm key để deduplicate

            if (!imageMap.has(imageKey)) {
                 imageMap.set(imageKey, {
                    id: img.image_id,
                    url: img.image_url,
                    isMain: img.is_main === 1,
                    color: color
                });
            } else {
                 // Nếu ảnh đã tồn tại, kiểm tra xem nó có phải là ảnh chính không
                 if (img.is_main === 1) {
                     imageMap.get(imageKey).isMain = true;
                 }
            }
        });

        // Gắn ảnh đã lọc vào nhóm màu tương ứng
        imageMap.forEach(imageRecord => {
            const color = imageRecord.color;
            if (groupedVariants[color]) {
                groupedVariants[color].images.push({
                    id: imageRecord.id,
                    url: imageRecord.url,
                    isMain: imageRecord.isMain,
                });
            }
        });

        // --- 3. Tổng hợp thành mảng Variants cuối cùng cho Vue State ---
        const finalVariants = Object.values(groupedVariants).map(group => {
            // Sắp xếp ảnh để ảnh chính (isMain: true) luôn ở đầu
            group.images.sort((a, b) => (a.isMain === b.isMain ? 0 : a.isMain ? -1 : 1));

            return reactive({
                id: group.variantIds.values().next().value, // Lấy variant_id đầu tiên làm ID đại diện nhóm màu
                color: group.color,
                isNew: false,
                newImages: [],
                existingImages: group.images,
                deletedImageIds: [],
                newImagePreviews: [],
                sizes: group.sizes.map(s => reactive({ ...s, isNew: false })),
                deletedSizeIds: [],
            });
        });

        // --- 4. Cập nhật dữ liệu chính thức ---
        product.nameProduct = fetchedProduct.name;
        // Chú ý: Backend trả price là 849000, nếu frontend cần dùng giá đó, giữ nguyên.
        // Nếu frontend chỉ cần giá nhỏ nhất, cần tính toán thêm. Hiện tại, dùng giá sản phẩm gốc.
        product.price = fetchedProduct.price;
        product.description = fetchedProduct.description;
        product.category_id = fetchedProduct.category_id;
        product.variants = finalVariants;

        // Lưu trạng thái ban đầu
        initialProductData.value = JSON.parse(JSON.stringify(toRaw(product)));

    } catch (e) {
        console.error('Lỗi khi tải dữ liệu sản phẩm:', e);
        alert('Không thể tải dữ liệu sản phẩm. Vui lòng thử lại.');
    } finally {
        isLoading.value = false;
    }
};

onMounted(async () => {
    await handleFetchCategories();
    await fetchProductData();
});

// --- VALIDATION & KIỂM TRA TRÙNG LẶP ---
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
        const colors = new Set();
        product.variants.forEach((v, vIndex) => {
            // 1. Kiểm tra trùng màu
            if (colors.has(v.color) && v.isNew) { // Chỉ cảnh báo trùng lặp nếu là biến thể mới
                 error.value[`variantColor_${vIndex}`] = 'Màu sắc này đã tồn tại trong các biến thể khác.';
                 isValid = false;
            } else {
                 colors.add(v.color);
                 error.value[`variantColor_${vIndex}`] = ''; // Xóa lỗi nếu không trùng
            }

            // 2. Kiểm tra có ảnh không
            if (v.newImages.length === 0 && v.existingImages.length === 0) {
                error.value[`variantImage_${vIndex}`] = 'Biến thể này chưa có hình ảnh nào.';
                isValid = false;
            }

            // 3. Kiểm tra trùng size trong cùng biến thể
            const sizes = new Set();
            v.sizes.forEach((s, sIndex) => {
                if (!s.size.trim()) {
                    error.value[`variantSize_${vIndex}_${sIndex}`] = 'Size không được để trống.';
                    isValid = false;
                } else if (sizes.has(s.size)) {
                    error.value[`variantSize_${vIndex}_${sIndex}`] = 'Size này đã tồn tại.';
                    isValid = false;
                } else {
                    sizes.add(s.size);
                    error.value[`variantSize_${vIndex}_${sIndex}`] = ''; // Xóa lỗi
                }
                if (!s.stock || s.stock < 0) {
                    error.value[`variantStock_${vIndex}_${sIndex}`] = 'Số lượng phải >= 0.';
                    isValid = false;
                }
                if (s.price && s.price < 0) {
                    error.value[`variantPrice_${vIndex}_${sIndex}`] = 'Giá phải >= 0.';
                    isValid = false;
                }
            });
        });
    }

    return isValid;
}

// --- PRODUCT STATE ---
const product = reactive({
    nameProduct: '',
    price: 0,
    description: '',
    category_id: null,
    variants: [], // Sẽ được fetch từ API
});

// --- IMAGE HANDLING ---
const handleVariantImageUpload = (event, vIndex) => {
    const files = Array.from(event.target.files);
    if(files.length === 0) return;

    files.forEach(file => {
        product.variants[vIndex].newImages.push(file); // Thêm vào mảng newImages
        product.variants[vIndex].newImagePreviews.push({ // Thêm vào mảng preview
            url: URL.createObjectURL(file),
            file: file,
        });
    });
    event.target.value = ''; // Reset input file
}

const removeNewVariantImage = (vIndex, imgIndex) => {
    URL.revokeObjectURL(product.variants[vIndex].newImagePreviews[imgIndex].url);
    product.variants[vIndex].newImagePreviews.splice(imgIndex, 1);
    product.variants[vIndex].newImages.splice(imgIndex, 1);
}

const removeExistingVariantImage = (vIndex, imgIndex) => {
    const image = product.variants[vIndex].existingImages[imgIndex];
    if (confirm(`Bạn có chắc chắn muốn xóa ảnh này?`)) {
        product.variants[vIndex].deletedImageIds.push(image.id); // Lưu ID để xóa trên server
        product.variants[vIndex].existingImages.splice(imgIndex, 1);
    }
}

// Đặt ảnh mới/ảnh cũ làm ảnh chính (luôn đưa lên đầu mảng tương ứng)
const setMainNewImage = (vIndex, imgIndex) => {
    const variant = product.variants[vIndex];
    const itemToMove = variant.newImagePreviews.splice(imgIndex, 1)[0];
    variant.newImagePreviews.unshift(itemToMove);

    const fileToMove = variant.newImages.splice(imgIndex, 1)[0];
    variant.newImages.unshift(fileToMove);
}

const setMainExistingImage = (vIndex, imgIndex) => {
    const variant = product.variants[vIndex];
    const itemToMove = variant.existingImages.splice(imgIndex, 1)[0];
    variant.existingImages.forEach(img => img.isMain = false); // Đặt tất cả về false
    itemToMove.isMain = true; // Đặt ảnh này là true
    variant.existingImages.unshift(itemToMove); // Đưa lên đầu
}

// --- CATEGORY HANDLING ---
const handleCategorySelected = (id) => {
    product.category_id = id;
}

// --- VARIANT HANDLING ---
const addVariant = () => {
    product.variants.push(reactive({
        id: null, // Biến thể mới chưa có ID
        color: '#FFFFFF', // Màu trắng mặc định cho biến thể mới
        isNew: true,
        newImages: [],
        existingImages: [],
        deletedImageIds: [],
        newImagePreviews: [],
        sizes: [reactive({ id: null, size: '', stock: 0, price: 0, isNew: true })],
        deletedSizeIds: [],
    }));
}

const removeVariant = (vIndex) => {
    if (confirm('Bạn có chắc chắn muốn xóa biến thể này?')) {
        const variantToRemove = product.variants[vIndex];
        if (variantToRemove.id) { // Nếu là biến thể cũ, thêm vào danh sách xóa
            // Cần thêm mảng deletedVariantIds vào product reactive
            // VD: product.deletedVariantIds.push(variantToRemove.id);
            // Hiện tại, tạm thời bỏ qua logic này vì cần update Product Model
            // và Controller để xử lý deletedVariantIds
            console.log(`Variant ID ${variantToRemove.id} sẽ được xóa.`);
        }
        product.variants.splice(vIndex, 1);
    }
}

const addSize = (vIndex) => {
    product.variants[vIndex].sizes.push(reactive({ id: null, size: '', stock: 0, price: 0, isNew: true }));
}

const removeSize = (vIndex, sIndex) => {
    if (confirm('Bạn có chắc chắn muốn xóa kích thước này?')) {
        const sizeToRemove = product.variants[vIndex].sizes[sIndex];
        if (sizeToRemove.id) {
            product.variants[vIndex].deletedSizeIds.push(sizeToRemove.id); // Lưu ID để xóa trên server
        }
        product.variants[vIndex].sizes.splice(sIndex, 1);
    }
}

// --- SUBMIT UPDATE ---
const handleUpdateProduct = async () => {
    if (!validateForm()) {
        return;
    }

    const formData = new FormData();
    formData.append('_method', 'PUT'); // Quan trọng: giả lập PUT request cho Laravel

    formData.append('name', product.nameProduct);
    formData.append('price', product.price);
    formData.append('description', product.description);
    formData.append('category_id', product.category_id);

    // Chuyển đổi variants để gửi đi
    const variantsPayload = toRaw(product.variants).map(variant => ({
        id: variant.id, // Gửi ID của biến thể nếu có
        color: variant.color,
        isNew: variant.isNew,
        deletedImageIds: variant.deletedImageIds,
        deletedSizeIds: variant.deletedSizeIds,
        sizes: variant.sizes.map(size => ({
            id: size.id, // Gửi ID của size nếu có
            size: size.size,
            stock: size.stock,
            price: size.price,
            isNew: size.isNew,
        })),
    }));
    formData.append('variants', JSON.stringify(variantsPayload));

    // Append các ảnh MỚI cho từng biến thể
    product.variants.forEach((variant, vIndex) => {
        const rawNewImages = toRaw(variant.newImages);
        if (rawNewImages && rawNewImages.length > 0) {
            rawNewImages.forEach((imageFile) => {
                if (imageFile instanceof File) {
                    formData.append(`new_variant_images[${vIndex}][]`, imageFile);
                }
            });
        }
    });

    // Console log để kiểm tra trước khi gửi
    console.log('--- DỮ LIỆU FORM ĐỂ CẬP NHẬT ---');
    for (let [key, value] of formData.entries()) {
        console.log(`${key}:`, value);
    }
    console.log('---------------------------------');

    try {
        const response = await api.post(`/products/${product_id}`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        }); // Sử dụng api.post với _method PUT

        if (response.data.status === true) {
            alert(response.data.message);
            router.push('/admin/products');
        } else {
            console.error('Lỗi từ API:', response.data.message);
            // Hiển thị lỗi từ server cho người dùng
            alert('Có lỗi xảy ra: ' + response.data.message);
        }
    } catch (err) {
        console.error('Lỗi khi gọi API:', err.response ? err.response.data : err);
        // Xử lý lỗi validation từ Laravel
        if (err.response && err.response.status === 422) {
            error.value = err.response.data.errors;
            alert('Vui lòng kiểm tra lại thông tin bạn đã nhập.');
        } else {
            alert('Có lỗi xảy ra khi cập nhật sản phẩm. Vui lòng thử lại.');
        }
    }
};

// Theo dõi thay đổi của biến thể để cảnh báo trùng lặp màu
watch(() => product.variants.map(v => v.color), (newColors, oldColors) => {
    // Kích hoạt validateForm khi màu sắc thay đổi
    // Để có thể hiển thị cảnh báo trùng lặp màu ngay lập tức
    validateForm();
}, { deep: true });

// Theo dõi thay đổi của size trong biến thể để cảnh báo trùng lặp size
watch(() => product.variants.map(v => v.sizes.map(s => s.size)), (newSizes, oldSizes) => {
    validateForm();
}, { deep: true });

</script>

<template>
    <div v-if="isLoading" class="d-flex justify-content-center align-items-center" style="min-height: 80vh;">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="ms-3 fs-5 text-primary">Đang tải dữ liệu sản phẩm...</p>
    </div>

    <div v-else class="container mt-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="fw-bold text-success m-0">
                <i class="bi bi-pencil-square me-2"></i>Cập nhật sản phẩm: {{ product.nameProduct }}
            </h3>
            <button @click="handleUpdateProduct" class="btn btn-success px-4 fw-bold">
                <i class="bi bi-arrow-clockwise me-2"></i>Cập nhật
            </button>
        </div>

        <form @submit.prevent="handleUpdateProduct" class="row g-4">
            <div class="col-lg-4">
                <div class="card shadow-sm border-0 sticky-top" style="top: 20px; z-index: 1;">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold m-0 text-uppercase text-secondary"><i class="bi bi-info-circle me-2"></i>Thông tin chung</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label fw-medium">Tên sản phẩm <span class="text-danger">*</span></label>
                            <input v-model="product.nameProduct" type="text" class="form-control"
                                placeholder="Ví dụ: Áo thun Polo..." />
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
                                        :level="0" :current-parent-id="product.category_id"
                                        @update:parent="handleCategorySelected" />
                                </ul>
                            </div>
                            <small v-if="error.category_id" class="text-danger">{{ error.category_id }}</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-medium">Mô tả chi tiết</label>
                            <textarea v-model="product.description" class="form-control" rows="5"
                                placeholder="Nhập mô tả sản phẩm..."></textarea>
                            <!-- <div ref="editor" class="bg-white border rounded"></div> -->
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div v-for="(variant, vIndex) in product.variants" :key="variant.id || `new-${vIndex}`"
                    class="card shadow-sm border-0 mb-4 position-relative overflow-hidden">
                    <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge" :class="variant.isNew ? 'bg-info' : 'bg-primary'">
                                <i :class="variant.isNew ? 'bi bi-plus-circle' : 'bi bi-tags'"></i>
                                Biến thể #{{ vIndex + 1 }}
                            </span>
                            <span class="fw-bold text-dark">Màu sắc:</span>
                            <input type="color" v-model="variant.color"
                                class="form-control form-control-color border-0 p-0" title="Chọn màu">
                            <small v-if="error[`variantColor_${vIndex}`]" class="text-danger ms-2 fw-normal">
                                <i class="bi bi-exclamation-triangle-fill"></i> {{ error[`variantColor_${vIndex}`] }}
                            </small>
                        </div>
                        <button v-if="product.variants.length > 1" type="button" class="btn btn-close"
                            @click="removeVariant(vIndex)"></button>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            <div class="col-12">
                                <label class="form-label fw-medium mb-1">Hình ảnh cho màu này</label>
                                <p class="text-muted small fst-italic mb-2">
                                    Ảnh đầu tiên sẽ là <strong>ảnh đại diện</strong> cho màu sắc này.
                                    Nhấn <i class="bi bi-star-fill text-warning"></i> để chọn ảnh chính.
                                </p>

                                <div class="upload-zone mb-3">
                                    <label class="btn btn-outline-primary btn-sm w-100 border-dashed py-3">
                                        <i class="bi bi-cloud-arrow-up fs-5 d-block"></i>
                                        <span>Tải ảnh mới lên (Chọn nhiều ảnh)</span>
                                        <input type="file" multiple class="d-none"
                                            @change="handleVariantImageUpload($event, vIndex)" accept="image/*">
                                    </label>
                                </div>

                                <div v-if="variant.existingImages.length > 0" class="d-flex flex-wrap gap-3 mb-3">
                                    <div v-for="(img, i) in variant.existingImages" :key="img.id"
                                        class="position-relative image-card" :class="{ 'is-main': img.isMain }">
                                        <img :src="'../../../../storage/' + img.url" class="rounded border" />

                                        <span v-if="img.isMain"
                                            class="position-absolute top-0 start-0 badge bg-warning text-dark m-1 shadow-sm">Ảnh chính</span>

                                        <div class="image-actions">
                                            <button v-if="!img.isMain" type="button"
                                                class="btn btn-sm btn-light text-warning mb-1"
                                                @click="setMainExistingImage(vIndex, i)" title="Đặt làm ảnh chính">
                                                <i class="bi bi-star-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger"
                                                @click="removeExistingVariantImage(vIndex, i)" title="Xóa ảnh">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div v-if="variant.newImagePreviews.length > 0" class="d-flex flex-wrap gap-3">
                                    <div v-for="(img, i) in variant.newImagePreviews" :key="i"
                                        class="position-relative image-card" :class="{ 'is-main': i === 0 && variant.existingImages.length === 0 }">
                                        <img :src="img.url" class="rounded border border-success" />
                                        <span v-if="i === 0 && variant.existingImages.length === 0"
                                            class="position-absolute top-0 start-0 badge bg-success text-white m-1 shadow-sm">Ảnh chính (mới)</span>

                                        <div class="image-actions">
                                            <button v-if="i !== 0 || variant.existingImages.length > 0" type="button"
                                                class="btn btn-sm btn-light text-warning mb-1"
                                                @click="setMainNewImage(vIndex, i)" title="Đặt làm ảnh chính">
                                                <i class="bi bi-star-fill"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger"
                                                @click="removeNewVariantImage(vIndex, i)" title="Xóa ảnh">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="variant.existingImages.length === 0 && variant.newImagePreviews.length === 0"
                                    class="text-center py-4 bg-light rounded text-muted small">
                                    Chưa có hình ảnh nào cho biến thể này.
                                </div>
                                <small v-if="error[`variantImage_${vIndex}`]" class="text-danger d-block mt-2">
                                    <i class="bi bi-exclamation-triangle-fill"></i> {{ error[`variantImage_${vIndex}`] }}
                                </small>
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
                                                <th style="width: 30%">Giá riêng (Optional)</th>
                                                <th style="width: 10%"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(size, sIndex) in variant.sizes" :key="size.id || `new-${sIndex}`">
                                                <td>
                                                    <input v-model="size.size" type="text"
                                                        class="form-control form-control-sm text-center"
                                                        :class="{ 'is-invalid': error[`variantSize_${vIndex}_${sIndex}`] }"
                                                        placeholder="S, M, L..." />
                                                    <small v-if="error[`variantSize_${vIndex}_${sIndex}`]"
                                                        class="text-danger text-start d-block">
                                                        {{ error[`variantSize_${vIndex}_${sIndex}`] }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <input v-model="size.stock" type="number"
                                                        class="form-control form-control-sm text-center"
                                                        :class="{ 'is-invalid': error[`variantStock_${vIndex}_${sIndex}`] }" />
                                                    <small v-if="error[`variantStock_${vIndex}_${sIndex}`]"
                                                        class="text-danger text-start d-block">
                                                        {{ error[`variantStock_${vIndex}_${sIndex}`] }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <input v-model="size.price" type="number"
                                                        class="form-control form-control-sm text-center"
                                                        :class="{ 'is-invalid': error[`variantPrice_${vIndex}_${sIndex}`] }"
                                                        placeholder="Mặc định" />
                                                    <small v-if="error[`variantPrice_${vIndex}_${sIndex}`]"
                                                        class="text-danger text-start d-block">
                                                        {{ error[`variantPrice_${vIndex}_${sIndex}`] }}
                                                    </small>
                                                </td>
                                                <td>
                                                    <button type="button" class="btn btn-link text-danger p-0"
                                                        @click="removeSize(vIndex, sIndex)">
                                                        <i class="bi bi-x-circle fs-5"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-outline-secondary btn-sm mt-2 w-100 border-dashed"
                                    @click="addSize(vIndex)">
                                    <i class="bi bi-plus"></i> Thêm kích thước
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <button type="button" class="btn btn-outline-success border-dashed w-100 py-3 fw-bold"
                    @click="addVariant">
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
    border: 1px solid #dee2e6; /* Thêm border cho đẹp */
    border-radius: .25rem;
}

.border-dashed {
    border-style: dashed !important;
}

.image-card {
    position: relative;
    width: 100px;
    height: 100px;
    overflow: hidden;
    cursor: pointer;
    border: 1px solid #e0e0e0;
    border-radius: 4px;
    transition: all 0.2s ease-in-out;
}

.image-card img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.image-card.is-main {
    border: 3px solid #ffc107 !important;
    box-shadow: 0 0 0 2px rgba(255, 193, 7, 0.5);
}

.image-actions {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    border-radius: 4px;
}

.image-card:hover .image-actions {
    opacity: 1;
}

.image-actions .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
    line-height: 1;
}
</style>
