<script setup>
import { ref, nextTick } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../axios'

const router = useRouter()
const open = ref(false)
const message = ref('')
const messages = ref([])
const loading = ref(false)
const scrollBox = ref(null)

const goToDetail = (id) => router.push(`/products/${id}`)

const scrollToBottom = () => {
  nextTick(() => {
    if (scrollBox.value) {
      scrollBox.value.scrollTop = scrollBox.value.scrollHeight
    }
  })
}

const toggleChat = () => {
  open.value = !open.value

  if (open.value && messages.value.length === 0) {
    setTimeout(() => {
      messages.value.push({
        from: 'bot',
        text: 'Xin chào 👋! Tôi có thể giúp gì cho bạn hôm nay?'
      })
      scrollToBottom()
    }, 200)
  }
}

const sendMessage = async () => {
  if (!message.value.trim()) return

  messages.value.push({ from: 'user', text: message.value })
  scrollToBottom()

  const userMsg = message.value
  message.value = ''

  loading.value = true

  const res = await api.post('/chat-ai', { message: userMsg })

  loading.value = false

  messages.value.push({
    from: 'bot',
    text: res.data.reply,
    products: res.data.products || []
  })

  scrollToBottom()
}
</script>

<template>
  <div class="chatbox-wrapper">
    
    <!-- Nút mở -->
    <button class="chat-toggle" @click="toggleChat">
      <i class="bi bi-chat-dots-fill"></i>
    </button>

    <!-- Chatbox -->
    <div v-if="open" class="chatbox">
      
      <!-- Header -->
      <div class="chat-header">
        <div class="title">
          <i class="bi bi-robot"></i> AI Hỗ Trợ
        </div>
        <span class="close-btn" @click="open = false">✖</span>
      </div>

      <!-- Messages -->
      <div class="messages" ref="scrollBox">

        <div 
          v-for="(msg, i) in messages" 
          :key="i" 
          class="message-block"
        >
          <div :class="['msg-item', msg.from]">
            <div class="bubble">{{ msg.text }}</div>
          </div>

          <!-- Product cards -->
          <div v-if="msg.products?.length" class="product-list">
            <div class="product-card" v-for="p in msg.products" :key="p.id">

              <div class="p-name">{{ p.name }}</div>
              <div class="p-price">{{ p.price.toLocaleString() }}₫</div>

              <button class="detail-btn" @click="goToDetail(p.id)">
                Xem chi tiết
              </button>
            </div>
          </div>
        </div>

        <!-- Typing animation -->
        <div v-if="loading" class="msg-item bot">
          <div class="bubble typing">
            <span></span><span></span><span></span>
          </div>
        </div>

      </div>

      <!-- Input -->
      <div class="input-box">
        <input 
          v-model="message" 
          @keyup.enter="sendMessage"
          placeholder="Nhập tin nhắn..."
        />
        <button @click="sendMessage" class="send-btn">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>

    </div>

  </div>
</template>

<style scoped>

/* ===============================
   BUTTON FLOAT
=============================== */
.chat-toggle {
  background: #0d6efd;
  width: 62px;
  height: 62px;
  border-radius: 50%;
  color: white;
  border: none;
  font-size: 26px;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  box-shadow: 0 4px 18px rgba(0,0,0,0.25);
  transition: 0.2s;
}
.chat-toggle:hover {
  transform: scale(1.08);
}

/* ===============================
   CHATBOX
=============================== */
.chatbox-wrapper {
  position: fixed;
  bottom: 20px;
  right: 20px;
}

.chatbox {
  width: 360px;
  height: 540px;
  background: #fff;
  border-radius: 16px;
  box-shadow: 0 4px 32px rgba(0,0,0,0.25);
  display: flex;
  flex-direction: column;
  overflow: hidden;
  animation: popup 0.25s ease-out;
}

@keyframes popup {
  from { transform: translateY(20px); opacity: 0; }
}

/* ===============================
   HEADER
=============================== */
.chat-header {
  background: #0d6efd;
  padding: 14px;
  color: white;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.title {
  font-size: 16px;
  font-weight: 600;
  display: flex;
  gap: 6px;
  align-items: center;
}

.close-btn {
  cursor: pointer;
}

/* ===============================
   MESSAGES
=============================== */
.messages {
  flex: 1;
  overflow-y: auto;
  padding: 14px;
  background: #f3f4f6;
}

.msg-item {
  display: flex;
  margin-bottom: 12px;
}

.msg-item.user {
  justify-content: flex-end;
}

.msg-item.bot {
  justify-content: flex-start;
}

.bubble {
  max-width: 75%;
  padding: 10px 14px;
  border-radius: 14px;
  font-size: 14px;
  line-height: 1.45;
  background: #e5e7eb;
}

.msg-item.user .bubble {
  background: #0d6efd;
  color: white;
}

/* ===============================
   PRODUCT CARDS
=============================== */
.product-list {
  margin-left: 35px;
  margin-top: 8px;
}

.product-card {
  background: white;
  padding: 10px 12px;
  border-radius: 12px;
  border: 1px solid #e0e0e0;
  margin-bottom: 10px;
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
}

.p-name {
  font-weight: 600;
}

.p-price {
  color: #d90429;
  font-size: 15px;
  margin: 4px 0 8px;
}

.detail-btn {
  width: 100%;
  padding: 8px;
  border: none;
  border-radius: 8px;
  background: #0d6efd;
  color: white;
  cursor: pointer;
  transition: 0.2s;
}
.detail-btn:hover {
  opacity: 0.9;
}

/* ===============================
   INPUT
=============================== */
.input-box {
  display: flex;
  border-top: 1px solid #ddd;
}
.input-box input {
  flex: 1;
  border: none;
  padding: 12px;
  font-size: 14px;
  outline: none;
}
.send-btn {
  background: #0d6efd;
  color: white;
  border: none;
  width: 55px;
  font-size: 20px;
  cursor: pointer;
}

/* ===============================
   TYPING DOTS
=============================== */
.typing span {
  width: 6px;
  height: 6px;
  background: #666;
  display: inline-block;
  border-radius: 50%;
  animation: blink 1.4s infinite both;
}
.typing span:nth-child(2) { animation-delay: 0.2s; }
.typing span:nth-child(3) { animation-delay: 0.4s; }

@keyframes blink {
  0%, 100% { opacity: .2; }
  20% { opacity: 1; }
}
</style>
