<script setup>
import api from "../../axios";
import { reactive, ref } from "vue";
import { useRouter } from "vue-router";

const router = useRouter();
const form = reactive({
    image: null,
    link: "",
    imagePreview: null,
});

const errors = ref({});

const onFileChange = (e) => {
    const file = e.target.files[0];
    if (file) {
        form.image = file;
        form.imagePreview = URL.createObjectURL(file);
    } else {
        form.image = null;
        form.imagePreview = null;
    }
};

const submitBanner = async () => {
    errors.value = {}; // Xóa lỗi cũ
    const formData = new FormData();

    if (form.image) {
        formData.append("images", form.image);
    }
    formData.append("link", form.link);

    try {
        const res = await api.post("/banners", formData, {
            headers: {
                "Content-Type": "multipart/form-data",
            },
        });

        if (res.status === 201) {
            alert("Thêm banner thành công!");
            // Tùy chọn: chuyển hướng hoặc reset form
            form.image = null;
            form.link = "";
            form.imagePreview = null;
            router.push('/admin/banners'); // Chuyển hướng nếu có trang danh sách
        }
    } catch (error) {
        if (error.response && error.response.data && error.response.data.errors) {
            errors.value = error.response.data.errors;
        } else {
            alert("Đã có lỗi không mong muốn xảy ra. Vui lòng thử lại.");
            console.error(error);
        }
    }
};
</script>

<template>
    <div class="container py-4">
        <h3 class="fw-bold text-primary mb-4">
            <i class="bi bi-card-image me-2"></i> Thêm Banner Mới
        </h3>
        <div class="card shadow-sm border-0 col-md-8 mx-auto">
            <div class="card-body p-4">
                <form @submit.prevent="submitBanner">
                    <div class="mb-3">
                        <label for="banner-image" class="form-label">Hình ảnh Banner</label>
                        <input type="file" class="form-control" id="banner-image" @change="onFileChange" />
                        <small v-if="errors.images" class="text-danger">{{ errors.images[0] }}</small>
                    </div>

                    <div v-if="form.imagePreview" class="mb-3 text-center">
                        <p class="form-label">Xem trước:</p>
                        <img :src="form.imagePreview" alt="Xem trước banner" class="img-fluid rounded shadow-sm"
                            style="max-height: 250px;" />
                    </div>

                    <div class="mb-4">
                        <label for="banner-link" class="form-label">Đường dẫn (URL)</label>
                        <input type="text" v-model="form.link" class="form-control" id="banner-link"
                            placeholder="https://example.com/san-pham (để trống nếu không có)" />
                        <small v-if="errors.link" class="text-danger">{{ errors.link[0] }}</small>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary py-2">
                            <i class="bi bi-plus-circle me-1"></i> Thêm Banner
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
