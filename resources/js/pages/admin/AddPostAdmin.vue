<script setup>
import { onMounted, ref, watch } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'
import api from '../../axios'

const form = ref({
    title: '',
    slug: '',
    metaDescription: '',
    content: '',
    thumbnail: null,
    // Thêm trường preview
    thumbnailPreview: null
})


const editor = ref(null)
let quill = null

const generateSlug = (text) => {
    return text
        .toLowerCase()
        .normalize('NFD')                 // tách dấu
        .replace(/[\u0300-\u036f]/g, '')  // xoá dấu
        .replace(/đ/g, 'd')
        .replace(/[^a-z0-9\s-]/g, '')     // xoá ký tự đặc biệt
        .trim()
        .replace(/\s+/g, '-')
}


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

const checkSlugUnique = async (baseSlug) => {
    try {
        const res = await api.get('/posts')
        const slugs = res.data.map(post => post.slug)

        if (!slugs.includes(baseSlug)) {
            return baseSlug
        }

        let count = 1
        let newSlug = `${baseSlug}-${count}`

        while (slugs.includes(newSlug)) {
            count++
            newSlug = `${baseSlug}-${count}`
        }

        return newSlug
    } catch (error) {
        console.error('Lỗi kiểm tra slug:', error)
        return baseSlug
    }
}
let slugTimeout = null

watch(() => form.value.title, (newTitle) => {
    if (!newTitle) {
        form.value.slug = ''
        return
    }
    const baseSlug = generateSlug(newTitle)

    clearTimeout(slugTimeout)
    slugTimeout = setTimeout(async () => {
        form.value.slug = await checkSlugUnique(baseSlug)
    }, 500)
})
const generateMetaDescription = (html) => {
    const div = document.createElement('div')
    div.innerHTML = html
    const text = div.textContent || div.innerText || ''
    return text.trim().substring(0, 150)
}
const isMetaEdited = ref(false)


const submitPost = async () => {
    form.value.content = quill.root.innerHTML
    if (!isMetaEdited.value || !form.value.metaDescription) {
        form.value.metaDescription = generateMetaDescription(form.value.content)
    }

    if (form.value.metaDescription.length > 160) {
        alert('Meta description không được vượt quá 160 ký tự')
        return
    }

    const data = new FormData()
    data.append('title', form.value.title)
    data.append('slug', form.value.slug)
    data.append('meta_description', form.value.metaDescription)
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
                        <input v-model="form.slug" type="text" class="form-control" placeholder="slug-bai-viet"
                            readonly />
                    </div>
                    <div>
                        <label class="form-label fw-bold">Meta description (SEO) </label>
                        <textarea v-model="form.metaDescription" class="form-control" rows="3"
                            @input="isMetaEdited = true" maxlength="160"
                            placeholder="Tối ưu SEO, 140-160 ký tự"></textarea>
                        <div style="font-size: 12px">
                            <span :style="{ color: form.metaDescription.length > 160 ? 'red' : '#666' }">
                                {{ form.metaDescription.length }}/160 ký tự
                            </span>
                        </div>
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
