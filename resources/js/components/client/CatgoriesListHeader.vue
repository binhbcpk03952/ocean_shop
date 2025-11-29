<script setup>
import { ref, onMounted, provide } from 'vue';
import api from '../../axios';
import CategoryListItem from './CategoryListItem.vue';
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
const expandedId = ref(null);
provide('expandedId', expandedId);
onMounted(() => {
    handleFetchCategories()
})
</script>
<template>
    <div class="row py-4">
        <div class="col-lg-3 box-cate" v-if="categories" v-for="category in categories" style="height: auto; max-height: 350px;">
            <div class="category-list" :key="category.category_id">
                <h5 class="ms-4 category-item fw-bold fs-4">{{ category.name }}</h5>
                <div v-if="category.children && category.children.length" class="list-children ms-3">
                    <CategoryListItem v-for="child in category.children"
                        :key="child.category_id"
                        :categories="child"
                        :lever="1" />
                        </div>
            </div>
        </div>
        <div class="col-lg-3 list-children" style="height: 350px; max-height: 350px; overflow-y: auto;">
            <h5 class="fs-4 fw-bold">Bộ sưu tập</h5>
            <div class="list">
                <div class="list-item my-2">
                    <img src="https://buggy.yodycdn.com/images/collection-horizontal-dt/8d6038269fe50341cc252610921d3f64.webp?width=597&height=336" alt="demo" class="w-100 rounded">
                    <h6 class="mt-2">Bộ sưu tập YODY X STREETWEAR</h6>
                </div>
                <div class="list-item">
                    <img src="https://buggy.yodycdn.com/images/collection-horizontal-dt/1bdf64f7ffdc397570ecc1b43dc0c3f2.webp?width=597&height=336" alt="demo" class="w-100 rounded">
                    <h6 class="mt-2">Bộ sưu tập YODY X STREETWEAR</h6>
                </div>
            </div>

        </div>
    </div>
</template>

<style scoped>
.list-children {
    border-right: 1px solid #ddd;
    padding-right: 15px;
    overflow-y: auto;
    max-height: 300px;
}
.list-children::-webkit-scrollbar {
    width: 3px;
}
.list-children::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 10px;
}
.list-children::-webkit-scrollbar-thumb {
    background: transparent;
    border-radius: 10px;
}
.category-list:hover > .list-children::-webkit-scrollbar-track {
  background: #f1f1f1;
  transition: 1s ease all;
}
.category-list:hover > .list-children::-webkit-scrollbar-thumb {
    background: #888;
    transition: 1s ease all;
}
</style>
