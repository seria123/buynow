<script setup>
import { ref, onMounted, nextTick } from 'vue'
import axios from 'axios'

const open = ref(false)
const message = ref('')
const bubbles = ref([])
const sending = ref(false)
const chatBody = ref(null)

// Each bubble: { id, type: 'user'|'support', text, followUps?, actions? }

const scrollToBottom = async () => {
  await nextTick()
  if (chatBody.value) {
    chatBody.value.scrollTop = chatBody.value.scrollHeight
  }
}

const buildBubbles = (msgs) => {
  const result = []
  msgs.forEach(msg => {
    if (msg.message) {
      result.push({ id: msg.id + '_msg', text: msg.message, type: 'user', user: msg.user })
    }
    if (msg.reply) {
      result.push({
        id: msg.id + '_reply',
        text: msg.reply,
        type: 'support',
        followUps: msg.followUps ?? [],
        actions: msg.actions ?? [],
      })
    }
  })
  return result
}

const fetchMessages = async () => {
  try {
    const res = await axios.get('/support/messages')
    const msgs = res.data.reverse()
    bubbles.value = buildBubbles(msgs)
    scrollToBottom()
  } catch (e) {
    console.error('Failed to fetch messages:', e)
  }
}

// Show welcome chips when chat opens for the first time (no prior messages)
const handleOpen = () => {
  open.value = !open.value
  if (open.value && bubbles.value.length === 0) {
    bubbles.value.push({
      id: 'welcome',
      type: 'support',
      text: "Hi there! 👋 Welcome to BuyNow Support. What can I help you with today?",
      followUps: ['Order Issue', 'Payment Issue', 'Return or Refund', 'Delivery & Shipping', 'Account & Profile', 'Product Inquiry'],
      actions: [],
    })
    scrollToBottom()
  }
}

const sendMessage = async (text) => {
  const userText = text ?? message.value
  if (!userText.trim()) return
  message.value = ''
  sending.value = true

  // Immediately push user bubble and clear last followUps
  bubbles.value.forEach(b => { b.followUps = []; b.actions = [] })
  bubbles.value.push({ id: 'u_' + Date.now(), text: userText, type: 'user' })
  scrollToBottom()

  try {
    const res = await axios.post('/support/message', { message: userText })
    const msg = res.data

    // Show typing animation delay then push reply
    setTimeout(() => {
      bubbles.value.push({
        id: msg.id + '_reply',
        type: 'support',
        text: msg.reply,
        followUps: msg.followUps ?? [],
        actions: msg.actions ?? [],
      })
      scrollToBottom()
    }, 700)
  } catch (e) {
    console.error('Failed to send message:', e)
    message.value = userText
    bubbles.value.pop()
  } finally {
    sending.value = false
  }
}

const handleKeydown = (e) => {
  if (e.key === 'Enter' && !e.shiftKey) {
    e.preventDefault()
    sendMessage()
  }
}

onMounted(() => {
  fetchMessages()
})
</script>

<template>
  <!-- Floating Chat Button -->
  <button
    @click="handleOpen"
    class="fixed bottom-6 right-6 z-50 bg-yellow-400 hover:bg-yellow-500 text-black p-4 rounded-full shadow-xl transition hover:scale-110"
    title="Customer Support"
  >
    💬
  </button>

  <!-- Chat Window -->
  <div
    v-if="open"
    class="fixed bottom-24 right-6 w-80 bg-white rounded-2xl shadow-2xl border border-gray-200 overflow-hidden z-50 flex flex-col"
    style="max-height: 520px;"
  >
    <!-- Header -->
    <div class="bg-yellow-400 text-black px-4 py-3 flex justify-between items-center font-semibold shrink-0">
      <div class="flex items-center gap-2">
        <span>💬</span>
        <span>Customer Support</span>
      </div>
      <button @click="open = false" class="hover:opacity-70 transition">✕</button>
    </div>

    <!-- Chat Body -->
    <div
      ref="chatBody"
      class="p-4 flex-1 overflow-y-auto text-sm text-gray-700 flex flex-col gap-3"
    >
      <!-- Message bubbles -->
      <template v-for="bubble in bubbles" :key="bubble.id">
        <div
          class="flex"
          :class="bubble.type === 'user' ? 'justify-end' : 'justify-start'"
        >
          <!-- Support bubble -->
          <div v-if="bubble.type === 'support'" class="flex items-end gap-2 max-w-[85%]">
            <div class="w-7 h-7 rounded-full bg-yellow-400 flex items-center justify-center text-xs font-bold shrink-0 mb-1">S</div>
            <div class="bg-gray-100 text-gray-800 rounded-2xl rounded-bl-sm px-3 py-2 leading-relaxed whitespace-pre-line">
              {{ bubble.text }}
            </div>
          </div>

          <!-- User bubble -->
          <div v-else class="max-w-[85%]">
            <div class="bg-yellow-400 text-black rounded-2xl rounded-br-sm px-3 py-2 leading-relaxed">
              {{ bubble.text }}
            </div>
          </div>
        </div>

        <!-- Follow-up quick replies (only for support bubbles) -->
        <div
          v-if="bubble.type === 'support' && bubble.followUps && bubble.followUps.length"
          class="flex flex-wrap gap-2 pl-9"
        >
          <button
            v-for="chip in bubble.followUps"
            :key="chip"
            @click="sendMessage(chip)"
            :disabled="sending"
            class="text-xs bg-white border border-yellow-400 text-yellow-700 hover:bg-yellow-50 rounded-full px-3 py-1 transition disabled:opacity-50"
          >
            {{ chip }}
          </button>
        </div>

        <!-- Action buttons (links) -->
        <div
          v-if="bubble.type === 'support' && bubble.actions && bubble.actions.length"
          class="flex flex-wrap gap-2 pl-9"
        >
          <a
            v-for="action in bubble.actions"
            :key="action.url"
            :href="action.url"
            class="text-xs bg-yellow-400 hover:bg-yellow-500 text-black font-semibold rounded-full px-3 py-1 transition"
          >
            {{ action.label }}
          </a>
        </div>
      </template>

      <!-- Typing indicator -->
      <div v-if="sending" class="flex justify-start">
        <div class="flex items-end gap-2">
          <div class="w-7 h-7 rounded-full bg-yellow-400 flex items-center justify-center text-xs font-bold shrink-0">S</div>
          <div class="bg-gray-100 rounded-2xl rounded-bl-sm px-4 py-3 flex gap-1 items-center">
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:0ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:150ms"></span>
            <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce" style="animation-delay:300ms"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- Input -->
    <div class="border-t border-gray-200 p-3 shrink-0">
      <div class="flex gap-2 items-end">
        <textarea
          v-model="message"
          rows="2"
          placeholder="Type your message... (Enter to send)"
          @keydown="handleKeydown"
          class="flex-1 border border-gray-300 rounded-xl p-2 text-sm focus:outline-none focus:ring-2 focus:ring-yellow-400 resize-none"
        ></textarea>
        <button
          @click="sendMessage()"
          :disabled="sending || !message.trim()"
          class="bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 disabled:cursor-not-allowed text-black p-2 rounded-xl transition shrink-0"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
          </svg>
        </button>
      </div>
    </div>
  </div>
</template>
