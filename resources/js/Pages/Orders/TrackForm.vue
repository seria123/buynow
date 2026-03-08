<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

const orderNumber = ref('');
const error = ref('');
const loading = ref(false);

const submit = () => {
  error.value = '';
  if (!orderNumber.value.trim()) {
    error.value = 'Please enter your order number.';
    return;
  }
  loading.value = true;
  router.post('/orders/track', { order_number: orderNumber.value.trim() }, {
    onError: (errs) => {
      error.value = errs.order_number || 'Order not found. Please check your order number.';
      loading.value = false;
    },
    onFinish: () => { loading.value = false; },
  });
};
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 flex items-center justify-center py-16 px-4">
      <div class="w-full max-w-lg">

        <!-- Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-16 h-16 bg-yellow-100 dark:bg-yellow-900/30 rounded-2xl mb-4">
            <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Track Your Order</h1>
          <p class="text-gray-500 dark:text-gray-400 mt-2">Enter your order number to see live tracking status</p>
        </div>

        <!-- Form Card -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm p-8">
          <form @submit.prevent="submit" class="space-y-5">
            <div>
              <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
                Order Number
              </label>
              <input
                v-model="orderNumber"
                type="text"
                placeholder="e.g. ORD-20240001"
                class="w-full border border-gray-300 dark:border-zinc-600 rounded-xl px-4 py-3 text-sm bg-white dark:bg-zinc-800 text-gray-900 dark:text-white focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 outline-none transition"
              />
              <p v-if="error" class="mt-2 text-red-500 text-sm flex items-center gap-1.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                </svg>
                {{ error }}
              </p>
            </div>

            <button type="submit" :disabled="loading"
              class="w-full bg-yellow-400 hover:bg-yellow-500 disabled:opacity-50 text-black font-bold py-3 rounded-xl transition flex items-center justify-center gap-2">
              <svg v-if="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
              </svg>
              {{ loading ? 'Searching…' : 'Track Order' }}
            </button>
          </form>

          <p class="text-center text-xs text-gray-400 mt-5">
            You can find your order number in your confirmation email or
            <a href="/profile/orders" class="text-yellow-600 hover:underline font-medium">My Orders</a>
          </p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
