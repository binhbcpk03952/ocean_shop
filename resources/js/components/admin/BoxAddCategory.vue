<script setup>
import { reactive, ref, onMounted, watch } from 'vue';
import api from '../../axios';
import CategoryFormItem from './CategoryFormItem.vue';

const props = defineProps({
    showBoxAddCategory: Boolean,
    mode: String,
})
const emit = defineEmits(['categoryAdded', 'closeBoxAddCate'])

const handleCloseBox = () => {
    emit('closeBoxAddCate')
}

const categories = reactive({
    category_id: null,
    name: '',
    parent: null,
    image: null,
})
const categoriesList = ref([])


const imagePreviewUrl = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        categories.image = file;
        // Tạo URL để xem trước ảnh
        imagePreviewUrl.value = URL.createObjectURL(file);
    } else {
        categories.image = null;
        imagePreviewUrl.value = null;
    }
}
const handleResetForm = () => {
    Object.assign(categories, {
        category_id: null,
        name: '',
        parent: null,
        image: null,
    });
    imagePreviewUrl.value = null;
}
const handleFetchCategories = async () => {
    try {
        const response = await api.get('/categories')
        if (response.status === 200) {
            categoriesList.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách danh mục:', error)
    }
}

watch(() => props.showBoxAddCategory, (newVal) => {
    if (newVal && props.mode === 'add') {
        handleFetchCategories();
        handleResetForm();
    }
})
onMounted(() => {
    handleFetchCategories()
})
const handleAddCategory = async () => {
    // 1. Validate dữ liệu
    if (!categories.name.trim()) {
        alert('Vui lòng nhập tên danh mục.');
        return;
    }
    // 2. Chuẩn bị FormData để gửi file
    const formData = new FormData();
    formData.append('name', categories.name);
    formData.append('image', categories.image);
    // Nếu có danh mục cha, bạn có thể thêm vào đây
    if (categories.parent) {
        formData.append('parent_id', categories.parent);
    }

    console.log('Dữ liệu FormData đã chuẩn bị để gửi đi:');
    for (let [key, value] of formData.entries()) {
        console.log(`- ${key}:`, value);
    }

    try {
        const response = await api.post('/categories', formData);
        if (response.data.success) {
            alert('Thêm danh mục thành công!');
            emit('closeBoxAddCate');
            emit('categoryAdded'); // Gửi sự kiện để component cha load lại danh sách
            handleResetForm();
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.');
        }
    } catch (error) {
        console.error('Lỗi khi thêm danh mục:', error);
        alert('Có lỗi xảy ra, vui lòng thử lại.');
    }
}
</script>

<template>
    <div class="modal face show d-block" v-if="showBoxAddCategory" tabindex="-1"
        style="background-color: rgb(0, 0, 0, 0.19);">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3 p-4 pt-2">
                <div class="modal-header">
                    <h5 class="modal-title text-success fw-bold">
                        <i class="bi bi-box-seam me-2"></i>{{ mode === 'add' ? 'Thêm' : 'Sửa' }} danh mục sản phẩm
                    </h5>
                    <button type="button" class="btn-close" @click="handleCloseBox"></button>
                </div>
                <div class="modal-body">
                    <form @submit.prevent="handleAddCategory">
                        <div class="mb-3">
                            <label for="name-categories" class="form-label">Tên danh mục</label>
                            <input type="text" class="form-control" id="name-categories" v-model="categories.name">
                        </div>
                        <div class="mb-3">
                            <label for="pCategory" class="form-label">Danh mục cha</label>
                            <ul class="list-unstyled " style="max-height: 300px; overflow-y: auto;">
                                <li>
                                    <input type="radio" id="cat-0" :value="null" v-model="categories.parent"
                                        class="form-check-input me-2" name="category">
                                    <label for="cat-0">Không</label>
                                </li>

                                <CategoryFormItem v-for="cat in categoriesList" :key="cat.category_id" :cat="cat"
                                    :level="0" :current-parent-id="categories.parent"
                                    @update:parent="categories.parent = $event" />
                            </ul>
                        </div>
                        <div class="mb-3">
                            <label for="image-categories" class="form-label">Ảnh</label>
                            <input type="file" class="form-control" id="image-categories" @change="handleFileChange" />
                        </div>
                        <div class="image-view mb-3" v-if="imagePreviewUrl">
                            <img :src="imagePreviewUrl" alt="Xem trước ảnh" class="img-fluid rounded"
                                style="max-height: 200px;">
                        </div>
                        <button type="submit" class="btn btn-success w-100 mt-4 py-2">{{ mode === 'add' ? 'Thêm' : 'Sửa'
                            }} danh mục</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>
