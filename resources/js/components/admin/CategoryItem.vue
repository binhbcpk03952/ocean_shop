<script setup>
import { defineProps, defineEmits } from 'vue';

// 1. Import chính component để kích hoạt đệ quy
import CategoryItem from './CategoryItem.vue';

const props = defineProps({
    cat: {
        type: Object,
        required: true
    },
    // level dùng để quản lý độ sâu và thụt lề
    level: {
        type: Number,
        default: 0
    }
})

const emit = defineEmits(['edit', 'delete'])

</script>
<template>
    <tr class="category-row">
        <td>
            {{ props.cat.category_id }}
        </td>

        <td :class="{ 'fs-5 fw-bold': props.level === 0, 'ps-4': props.level > 0 }">
            <span v-if="props.level > 0" :class="`text-secondary me-2`">{{ '--'.repeat(props.level) }}</span>
            {{ props.cat.name }}
        </td>
        <td>
            <img :src="'../../../../storage/' + props.cat.image" alt="image" width="50" class="rounded">
        </td>

        <td>
            <span class="badge text-bg-dark" v-if="props.cat.parent_id === null">
                Không
            </span>
            <span v-else>{{ props.cat.parent_id }}</span>
        </td>

        <td class="text-end">
            <button @click="emit('edit', props.cat)" class="btn btn-sm btn-outline-secondary">Edit</button>
            <button @click="emit('delete', props.cat)" class="btn btn-sm btn-outline-danger ms-1">Delete</button>
        </td>
    </tr>

    <template v-if="props.cat.children && props.cat.children.length">
        <CategoryItem v-for="child in props.cat.children" :key="child.category_id" :cat="child" :level="props.level + 1"
            @edit="emit('edit', $event)" @delete="emit('delete', $event)" />
    </template>
</template>


<style scoped>
/* Optional: Thêm một số style nếu cần */
.category-row:hover {
    background-color: #f8f9fa;
}
</style>
