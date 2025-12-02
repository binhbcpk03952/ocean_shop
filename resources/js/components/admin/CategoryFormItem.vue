<script setup>
import { defineProps, defineEmits, computed } from 'vue';

// Import chính component để kích hoạt đệ quy
import CategoryFormItem from './CategoryFormItem.vue'; 

const props = defineProps({
    cat: {
        type: Object,
        required: true
    },
    level: {
        type: Number,
        default: 0
    },
    // Prop V-Model cho lựa chọn danh mục cha hiện tại (tên là current-parent-id để tránh xung đột)
    currentParentId: {
        type: [Number, String, Object],
        default: null
    }
});

const emit = defineEmits(['update:parent']);

// Computed property để xác định class thụt lề
const indentClass = computed(() => ({
    'ms-4': props.level > 0,
    // Thêm các class khác nếu cần
}));

// Hàm xử lý thay đổi radio button
const handleParentChange = (id) => {
    // Phát sự kiện lên component cha để cập nhật v-model
    emit('update:parent', id);
};
</script>

<template>
    <li :class="indentClass" :key="props.cat.category_id">
        <div class="form-check mt-1">
            <input 
                type="radio" 
                class="form-check-input me-2" 
                :id="'form-cat-' + props.cat.category_id"
                name="category" 
                :value="props.cat.category_id" 
                :checked="props.cat.category_id === props.currentParentId"
                @change="handleParentChange(props.cat.category_id)"
            />
            <label class="form-check-label" :for="'form-cat-' + props.cat.category_id">
                <span v-if="props.level > 0" class="text-secondary me-1">{{ '— '.repeat(props.level) }}</span>
                {{ props.cat.name }}
            </label>
        </div>
        
        <ul v-if="props.cat.children && props.cat.children.length" class="list-unstyled">
            <CategoryFormItem 
                v-for="child in props.cat.children" 
                :key="child.category_id"
                :cat="child"
                :level="props.level + 1"
                :current-parent-id="props.currentParentId"
                @update:parent="handleParentChange"
            />
        </ul>
    </li>
</template>