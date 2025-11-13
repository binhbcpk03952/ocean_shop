<script setup>
import { ref, onMounted } from 'vue';
import BoxAddCategory from '../components/admin/BoxAddCategory.vue';
import CategoryItem from '../components/admin/CategoryItem.vue';
import api from '../axios';
const mode = ref('add')
const showBoxCategory = ref(false)
const handleCloseBoxAddCate = () => {
    showBoxCategory.value = false
}
const categories = ref([])
const handleFetchCategories = async () => {
    try {
        const response = await api.get('/categories')
        if (response.status === 200) {
            categories.value = response.data
        } else {
            alert('Có lỗi xảy ra, vui lòng thử lại.')
        }
    } catch (error) {
        console.error('Lỗi khi lấy danh sách danh mục:', error)
    }
}
const handleEditCategory = (cateData) => {
    mode.value = 'edit'
    showBoxCategory.value = true

}

const handleDeleteCategory = async (cateData) => {
    const isConfirm = confirm('Bạn chắc chắn muốn xoá danh mục id=' + cateData.category_id + 'này?');
    if (isConfirm) {
        try {

        } catch (err) {
            console.error('Loi khi goi API: ' + err);
        }
    }
}
onMounted(() => {
    handleFetchCategories()
})


</script>
<template>
    <box-add-category :showBoxAddCategory="showBoxCategory" :mode="mode" @closeBoxAddCate="handleCloseBoxAddCate" @categoryAdded="handleFetchCategories"/>
    <div class="row mt-3">
        <h2>Quản lí danh mục sản phẩm</h2>
        <div class="text-end">
            <button class="btn btn-primary" @click="showBoxCategory = true">Thêm danh mục</button>
        </div>
        <div class="list-categories">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Tên danh mục</th>
                        <th>Hình ảnh</th>
                        <th>ID danh mục cha</th>
                        <th class="text-end">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <CategoryItem v-for="cat in categories"
                                :key="cat.category_id"
                                :cat="cat"
                                :level="0"
                                @edit="handleEditCategory($event)"
                                @delete="handleDeleteCategory($event)"/>
                </tbody>
                <tbody>

                </tbody>
            </table>
        </div>
    </div>
</template>
