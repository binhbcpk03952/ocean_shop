<script setup>
import { ref, onMounted } from 'vue';
import BoxAddCategory from '../../components/admin/BoxAddCategory.vue';
import CategoryItem from '../../components/admin/CategoryItem.vue';
import api from '../../axios';
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
            const res = await api.delete(`/categories/${cateData.category_id}`);
            if (res.status === 200 && res.data.status) {
                alert('Xoá danh mục thành công');
                // Cập nhật lại danh sách sau khi xoá
                handleFetchCategories();
            } else if (res.status === 403) {
                alert(res.data.message || 'Xoá danh mục thất bại');
            }
        } catch (err) {
            console.error('Loi khi goi API: ' + err);
            alert(err.response.data.message || 'Đã có lỗi xảy ra khi xoá danh mục');
        }
    }
}
onMounted(() => {
    handleFetchCategories()
})


</script>
<template>
    <box-add-category :showBoxAddCategory="showBoxCategory" :mode="mode" @closeBoxAddCate="handleCloseBoxAddCate"
        @categoryAdded="handleFetchCategories" />
    <div class="row mt-3">
        <div class="heading-sticky">
            <h2>Quản lí danh mục sản phẩm</h2>
            <div class="text-end">
                <button class="btn btn-primary" @click="showBoxCategory = true">
                    <i class="bi bi-plus-circle"></i> Thêm danh mục
                </button>
            </div>
        </div>
        <div class="list-categories mt-5">
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
                    <CategoryItem v-for="(cat, index) in categories" :key="cat.category_id" :index="index" :cat="cat"
                        :level="0" @edit="handleEditCategory($event)" @delete="handleDeleteCategory($event)" />
                </tbody>
            </table>
        </div>
    </div>
</template>

<style scoped>
.row {
    display: flex;
    flex-direction: column;
}

.heading-sticky {
    position: sticky;
    top: 0;
    background-color: #fff;
    padding: 1rem;
    border-bottom: 2px solid #e9ecef;
    z-index: 100;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.heading-sticky h2 {
    margin: 0;
    font-size: 1.75rem;
    font-weight: 600;
    color: #333;
}

.list-categories {
    flex: 1;
    overflow-x: auto;
}

.table {
    margin-bottom: 0;
}



@media (max-width: 768px) {
    .heading-sticky {
        flex-direction: column;
        gap: 1rem;
        align-items: flex-start;
    }

    .heading-sticky .text-end {
        width: 100%;
    }

    .heading-sticky .text-end button {
        width: 100%;
    }
}
</style>
