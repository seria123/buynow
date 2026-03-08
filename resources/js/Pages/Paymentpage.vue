
<script setup>
import { ref } from 'vue';
import { Inertia } from '@inertiajs/inertia';
import axios from 'axios';

const props = defineProps({
  order: Object,
  total: Number
});

const phone = ref('');
const loading = ref(false);
const message = ref('');
const messageType = ref('text-green-600');

const pay = async () => {
  if (!phone.value || phone.value.length !== 12) {
    message.value = 'Enter a valid phone number in format 2547XXXXXXXX';
    messageType.value = 'text-red-600';
    return;
  }

  loading.value = true;
  message.value = '';

  try {
    const response = await axios.post(`/payments/${props.order.id}/mpesa`, {
      phone: phone.value
    });

    // STK Push was initiated
    message.value = 'STK Push sent! Check your phone to complete payment.';
    messageType.value = 'text-green-600';
  } catch (error) {
    console.error(error);
    message.value = 'Payment failed. Please try again.';
    messageType.value = 'text-red-600';
  } finally {
    loading.value = false;
  }
};
</script>
<template>
  <div class="max-w-md mx-auto p-6 bg-white shadow rounded mt-10">
    <h1 class="text-2xl font-bold mb-4">Pay for Order #{{ order.order_number }}</h1>
    <p class="mb-2">Total Amount: <strong>KES {{ total }}</strong></p>

    <div v-if="message" :class="messageType" class="p-2 rounded mb-4">
      {{ message }}
    </div>

    <input
      v-model="phone"
      type="text"
      placeholder="2547XXXXXXXX"
      class="w-full border p-2 mb-4 rounded"
    />

    <button
      @click="pay"
      :disabled="loading"
      class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold px-4 py-2 rounded w-full"
    >
      {{ loading ? 'Processing...' : 'Pay with M-Pesa' }}
    </button>
  </div>
</template>
