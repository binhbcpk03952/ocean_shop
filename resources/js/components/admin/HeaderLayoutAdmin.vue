<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const user = null
const dropdown = ref(false);
const dropdownRef = ref(null);

const handleClickOutside = (event) => {
    if (dropdown.value && dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        dropdown.value = false;
    }
};

const handleDropdown = () => {
    dropdown.value = !dropdown.value;
}
onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});


</script>

<template>
    <div class="d-flex justify-content-between align-items-center py-3">
        <div class="search ms-5 position-relative d-flex align-items-center">
            <input type="text" id="search" placeholder="Tìm kiếm..."></input>
            <button>
                <i class="bi bi-search fs-5"></i>
            </button>
        </div>
        <router-link to="/" class="home text-black text-decoration-none">
            <i class="bi bi-house-door fs-4"></i>
            <span>Trang chủ</span>
        </router-link>
        <div class="group-item d-flex align-items-center">
            <div class="notication me-2">
                <i class="bi bi-bell fs-4"></i>
            </div>
            <div class="user position-relative" ref="dropdownRef">
                <span @click="handleDropdown" style="cursor: pointer;">
                    <i class="bi bi-person fs-4"></i> Admin
                </span>
                <div class="drop-down mt-2" v-show="dropdown">
                    <ul class="p-0">
                        <li class="nav-link outline-hover ps-3 py-2">
                            <button class="btn-logout">Đăng xuất</button>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
    <hr class="m-0">
</template>

<style scoped>
#search {
    width: 300px;
    height: 38px;
    padding: 0 5px 0 15px;
    border-radius: 23px;
    border: 1px solid black;
}

#search:focus {
    outline: none;
}

.search>button {
    background-color: transparent;
    border: none;
    cursor: pointer;
    position: absolute;
    right: 9px;
}

.drop-down {
    position: absolute;
    top: 30px;
    right: 0;
    background-color: white;
    border: 1px solid #ddd;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    flex-direction: column;
    min-width: 180px;
    z-index: 1000;
    width: 100%;
    min-height: 100%;
}

.btn-logout {
    background-color: transparent;
    border: none;
}

.outline-hover:hover {
    background-color: rgb(0, 0, 0, 0.1);
}

.outline-hover:hover .btn-logout {
    color: #3497E0;
}
</style>
