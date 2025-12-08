<!-- src/components/ConfirmBox.vue -->
<script setup>
import { defineProps, defineEmits } from 'vue'

const props = defineProps({
  show: Boolean,
  title: { type: String, default: 'Xác nhận hành động' },
  message: { type: String, default: '' }
})
const emit = defineEmits(['confirm', 'cancel'])

const confirm = () => emit('confirm')
const cancel = () => emit('cancel')

</script>

<template>
  <div v-if="show" class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.4);">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content text-center shadow-lg border-0">
        <div class="modal-body p-4">
          <i class="bi bi-question-circle-fill text-danger" style="font-size: 3rem;"></i>
          <p class="text-muted mb-4 fw-medium">{{ message }}</p>
          <div class="d-flex justify-content-center gap-3">
            <button class="btn btn-secondary px-4 rounded-5" @click="cancel">Hủy</button>
            <button class="btn bg-main text-white px-4 rounded-5" @click="confirm">Đồng ý</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-content {
  border-radius: 1rem;
}
</style>
