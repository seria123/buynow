<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

const props = defineProps({
  order: Object,
  statusSteps: Array,
});

const currentIndex = computed(() => props.statusSteps.indexOf(props.order.status));

const stepIcon = (step) => {
  const icons = {
    pending:    'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
    processing: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
    shipped:    'M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0',
    delivered:  'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
  };
  return icons[step] || icons.pending;
};

const stepLabel = (step) => {
  return step.charAt(0).toUpperCase() + step.slice(1);
};

const stepDesc = (step) => {
  const desc = {
    pending:    'Order received, awaiting confirmation',
    processing: 'We are preparing your order',
    shipped:    'Your order is on the way',
    delivered:  'Order delivered successfully',
  };
  return desc[step] || '';
};

const getStepState = (index) => {
  if (index < currentIndex.value) return 'done';
  if (index === currentIndex.value) return 'active';
  return 'upcoming';
};

const statusColor = computed(() => {
  const map = {
    pending:    'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-300',
    processing: 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
    shipped:    'bg-purple-100 text-purple-700 dark:bg-purple-900/30 dark:text-purple-300',
    delivered:  'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-300',
  };
  return map[props.order.status] || 'bg-gray-100 text-gray-700';
});

const paymentBadge = computed(() => {
  const map = {
    paid:    'bg-green-100 text-green-700',
    pending: 'bg-yellow-100 text-yellow-700',
    failed:  'bg-red-100 text-red-700',
  };
  return map[props.order.payment_status] || 'bg-gray-100 text-gray-700';
});
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto space-y-6">

        <!-- Back -->
        <Link href="/orders/track" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-yellow-600 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Track another order
        </Link>

        <!-- Order header card -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm p-6">
          <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
              <h1 class="text-xl font-bold text-gray-900 dark:text-white">Order #{{ order.order_number }}</h1>
              <p class="text-sm text-gray-400 mt-1">
                Placed on {{ new Date(order.created_at).toLocaleDateString('en-KE', { year: 'numeric', month: 'long', day: 'numeric' }) }}
              </p>
            </div>
            <div class="flex gap-2">
              <span :class="['text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide', statusColor]">
                {{ order.status }}
              </span>
              <span :class="['text-xs font-bold px-3 py-1 rounded-full capitalize', paymentBadge]">
                {{ order.payment_status }}
              </span>
            </div>
          </div>
        </div>

        <!-- Status Timeline -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm p-8">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-8">Order Status</h2>

          <div class="relative">
            <!-- Connecting line -->
            <div class="absolute top-6 left-6 right-6 h-0.5 bg-gray-200 dark:bg-zinc-700 hidden sm:block" style="left: calc(12.5%); right: calc(12.5%);"></div>
            <div class="absolute top-6 bg-yellow-400 h-0.5 hidden sm:block transition-all duration-500"
              :style="`left: calc(12.5%); width: calc(${(currentIndex / (statusSteps.length - 1)) * 75}%)`">
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-6 relative z-10">
              <div v-for="(step, index) in statusSteps" :key="step" class="flex flex-col items-center text-center">

                <!-- Circle icon -->
                <div :class="[
                  'w-12 h-12 rounded-2xl flex items-center justify-center mb-3 transition-all duration-300',
                  getStepState(index) === 'done'     ? 'bg-yellow-400 shadow-lg shadow-yellow-400/30' :
                  getStepState(index) === 'active'   ? 'bg-yellow-400 ring-4 ring-yellow-400/30 shadow-lg shadow-yellow-400/30' :
                                                       'bg-gray-100 dark:bg-zinc-800'
                ]">
                  <svg :class="[
                    'w-5 h-5',
                    getStepState(index) === 'upcoming' ? 'text-gray-400 dark:text-gray-500' : 'text-black'
                  ]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="stepIcon(step)"/>
                  </svg>
                </div>

                <p :class="[
                  'font-semibold text-sm',
                  getStepState(index) === 'upcoming' ? 'text-gray-400 dark:text-gray-500' : 'text-gray-900 dark:text-white'
                ]">{{ stepLabel(step) }}</p>

                <p class="text-xs text-gray-400 mt-0.5 leading-tight">{{ stepDesc(step) }}</p>

                <!-- Active pulse dot -->
                <div v-if="getStepState(index) === 'active'" class="mt-2 w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm p-6">
          <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-5">Items Ordered</h2>
          <div class="space-y-4">
            <div v-for="item in order.order_items" :key="item.id"
              class="flex items-center gap-4 p-4 bg-gray-50 dark:bg-zinc-800 rounded-2xl">
              <img
                v-if="item.product && item.product.thumbnail_url"
                :src="item.product.thumbnail_url"
                class="w-16 h-16 object-cover rounded-xl flex-shrink-0"
              />
              <div v-else class="w-16 h-16 bg-gray-200 dark:bg-zinc-700 rounded-xl flex-shrink-0 flex items-center justify-center">
                <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div class="flex-1 min-w-0">
                <p class="font-semibold text-gray-900 dark:text-white truncate">{{ item.product_name }}</p>
                <p class="text-sm text-gray-500">Qty: {{ item.quantity }}</p>
              </div>
              <p class="font-bold text-gray-900 dark:text-white flex-shrink-0">KES {{ Number(item.subtotal).toLocaleString() }}</p>
            </div>
          </div>

          <div class="border-t border-gray-100 dark:border-zinc-800 mt-5 pt-4">
            <div class="flex justify-between font-bold text-gray-900 dark:text-white">
              <span>Total</span>
              <span>KES {{ Number(order.total_amount || order.order_items?.reduce((s, i) => s + Number(i.subtotal), 0)).toLocaleString() }}</span>
            </div>
          </div>
        </div>

        <!-- CTA -->
        <div class="text-center">
          <Link href="/profile/orders"
            class="inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-bold px-6 py-3 rounded-xl transition">
            View All Orders
          </Link>
        </div>

      </div>
    </div>
  </MainLayout>
</template>
