<script setup>
import { onMounted, ref } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'
import api from '../../axios'

const form = ref({
    title: '',
    slug: '',
    content: '',
    thumbnail: null,
    // Thêm trường preview
    thumbnailPreview: null
})

const editor = ref(null)
let quill = null

onMounted(() => {
    quill = new Quill(editor.value, {
        theme: 'snow',
        placeholder: 'Nhập nội dung bài viết...',
        modules: {
            toolbar: [
                [{ header: [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ list: 'ordered' }, { list: 'bullet' }],
                ['link', 'image'],
            ],
        },
    })
})

const onThumbnailChange = (e) => {
    const file = e.target.files[0];
    form.value.thumbnail = file;

    // Tạo URL tạm thời để hiển thị preview
    if (file) {
        form.value.thumbnailPreview = URL.createObjectURL(file);
    } else {
        form.value.thumbnailPreview = null;
    }
}

const submitPost = async () => {
    form.value.content = quill.root.innerHTML

    const data = new FormData()
    data.append('title', form.value.title)
    data.append('slug', form.value.slug)
    data.append('content', form.value.content)

    if (form.value.thumbnail) {
        data.append('thumbnail', form.value.thumbnail)
    }

    console.log('Submit:', form.value)
    try {
        const response = await api.post('/posts', data, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        if (response.status === 200) {
            alert(response.data.message)
        } else {
            alert(response.data.message)
        }
    } catch (error) {
        console.error('Lỗi khi đăng bài:', error)
    }
}
</script>
<template>
    <div class="container py-4">
        <h1 class="h2 mb-4">Thêm Bài Viết (Quill)</h1>

        <div class="row">

            <div class="col-12 col-lg-9 mb-4">
                <div class="d-flex flex-column gap-3">

                    <div>
                        <label class="form-label fw-bold">Tiêu đề</label>
                        <input v-model="form.title" type="text" class="form-control"
                            placeholder="Nhập tiêu đề bài viết" />
                    </div>

                    <div>
                        <label class="form-label fw-bold">Slug</label>
                        <input v-model="form.slug" type="text" class="form-control" placeholder="slug-bai-viet" />
                    </div>

                    <div>
                        <label class="form-label fw-bold">Nội dung bài viết</label>
                        <div ref="editor" class="bg-white border rounded"></div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-3 mb-4">
                <div class="d-flex flex-column gap-3">

                    <div class="p-3 border rounded bg-light">
                        <label class="form-label fw-bold h5">Ảnh đại diện</label>
                        <input type="file" @change="onThumbnailChange" class="form-control form-control-sm" />

                        <div v-if="form.thumbnailPreview" class="mt-3">
                            <img :src="form.thumbnailPreview" alt="Thumbnail Preview" class="img-fluid rounded" />
                        </div>
                    </div>

                    <button @click="submitPost" class="btn btn-success w-100 py-2">
                        Đăng bài
                    </button>

                </div>
            </div>
        </div>
    </div>
</template>



<style>
/* Đảm bảo chiều cao tối thiểu cho Quill Editor */
.ql-editor {
    min-height: 400px;
}
</style>
