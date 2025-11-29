<script setup>
import { ref, inject } from 'vue';
import CategoryListItem from './CategoryListItem.vue';

const expandedId = inject('expandedId');

const props = defineProps({
    categories: Object,
    level: {
        type: Number,
        default: 0
    }
});

const toggleExpand = (id) => {
    expandedId.value = expandedId.value === id ? null : id;
};
</script>

<template>
    <ul class="list-category p-0 m-0 list-unstyled">
        <li class="d-flex justify-content-between align-items-center category-item">
            <span class="category-name" :class="{ 'fw-600': level === 0, 'text-small': level > 0 }"
                @click="toggleExpand(props.categories.category_id)">
                {{ props.categories.name }}
            </span>

            <i v-if="props.categories.children && props.categories.children.length > 0"
                class="bi bi-chevron-right toggle-icon" :class="{ rotate: expandedId === props.categories.category_id }"
                @click="toggleExpand(props.categories.category_id)"></i>
        </li>

        <transition name="slide">
            <div v-show="expandedId === props.categories.category_id" class="list-children ms-3">
                <CategoryListItem v-for="child in props.categories.children" :key="child.category_id"
                    :categories="child" :level="level + 1" />
            </div>
        </transition>
    </ul>
</template>

<style scoped>
.list-category {
    list-style: none;
}

.category-item {
    padding: 0.5rem 0;
    border-radius: 4px;
    transition: background-color 0.2s ease;
    user-select: none;
}

.category-item:hover {
    background-color: #f8f9fa;
}

.category-name {
    flex: 1;
    cursor: pointer;
    color: #333;
    font-weight: 500;
    padding: 0.5rem;
    border-radius: 4px;
    transition: color 0.2s ease, background-color 0.2s ease;
}

.category-name:hover {
    color: #3497E0;
    background-color: #e7f1f8;
}

.toggle-icon {
    font-size: 1.2rem;
    color: #3497E0;
    transition: transform 0.3s ease;
    padding: 0.25rem 0.5rem;
    display: flex;
    align-items: center;
    justify-content: center;
}

.toggle-icon.rotate {
    transform: rotate(90deg);
}

.list-children {
    overflow: hidden;
    margin-top: -10px;
}

/* Transition animation */
.slide-enter-active,
.slide-leave-active {
    transition: all 0.3s ease;
}

.slide-enter-from {
    opacity: 0;
    max-height: 0;
}

.slide-enter-to {
    opacity: 1;
    max-height: 500px;
}

.slide-leave-from {
    opacity: 1;
    max-height: 500px;
}

.slide-leave-to {
    opacity: 0;
    max-height: 0;
}

@media (max-width: 768px) {
    .category-item {
        padding: 0.25rem 0;
    }

    .category-name {
        padding: 0.25rem;
        font-size: 0.95rem;
    }

    .toggle-icon {
        font-size: 1rem;
        padding: 0.25rem;
    }
}

.text-small {
    font-size: 15px;
    font-weight: 400;
}
</style>
