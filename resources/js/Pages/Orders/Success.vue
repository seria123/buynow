<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

// Props from backend
const props = defineProps({
  order: { type: Object, required: true },
  summary: { type: Object, required: true },
});

// Reactive state
const orderState = ref({ ...props.order });
const summaryState = ref({ ...props.summary });

// Format date
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// Get status color
function getStatusColor(status) {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
}

// Get payment status color
function getPaymentStatusColor(status) {
  const colors = {
    unpaid: 'bg-yellow-100 text-yellow-800',
    pending: 'bg-blue-100 text-blue-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-purple-100 text-purple-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
}

// Continue shopping
const continueShopping = () => {
  router.visit('/products');
};

// View order details
const viewOrder = () => {
  router.visit(route('orders.show', orderState.value.id));
};
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="Order Confirmed!" />

      <div class="container mx-auto p-4">
        <!-- Success Header -->
        <div class="text-center mb-8">
          <div class="inline-flex items-center justify-center w-20 h-20 bg-green-100 rounded-full mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <h1 class="text-3xl font-bold text-green-600 mb-2">Order Confirmed! 🎉</h1>
          <p class="text-gray-600 dark:text-gray-400">Thank you for your purchase!</p>
        </div>

        <!-- Order Info Card -->
        <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-lg p-6 mb-6">
          <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
            <div>
              <h2 class="text-xl font-bold mb-2">Order #{{ orderState.order_number }}</h2>
              <p class="text-gray-500 text-sm">Placed on: {{ formatDate(orderState.created_at) }}</p>
            </div>
            <div class="flex gap-2 mt-4 md:mt-0">
              <span :class="getStatusColor(orderState.status)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ orderState.status }}
              </span>
              <span :class="getPaymentStatusColor(orderState.payment_status)" class="px-3 py-1 rounded-full text-sm font-medium">
                {{ orderState.payment_status }}
              </span>
            </div>
          </div>

          <!-- Order Items -->
          <div class="mb-6">
            <h3 class="font-bold text-lg mb-4">Order Items</h3>
            <div class="space-y-4">
              <div v-for="item in orderState.order_items" :key="item.id"
                   class="flex items-center justify-between border-b dark:border-zinc-700 pb-4 last:border-b-0">
                <div class="flex items-center gap-4">
                  <img
                    :src="item.product?.thumbnail_url || '/images/placeholder.svg'"
                    alt="Product Image"
                    class="w-16 h-16 object-cover rounded-md"
                  />
                  <div>
                    <div class="font-semibold">{{ item.product_name }}</div>
                    <div class="text-gray-500 text-sm">Qty: {{ item.quantity }}</div>
                  </div>
                </div>
                <div class="font-bold text-yellow-600">
                  KES {{ (item.price * item.quantity).toFixed(2) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Order Summary -->
          <div class="border-t dark:border-zinc-700 pt-6">
            <h3 class="font-bold text-lg mb-4">Order Summary</h3>
            <div class="space-y-3 max-w-md ml-auto">
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Subtotal:</span>
                <span class="font-semibold">KES {{ summaryState.itemsTotal.toFixed(2) }}</span>
              </div>
              
              <!-- Discount -->
              <div v-if="summaryState.discount > 0" class="flex justify-between text-green-600">
                <span>Discount {{ orderState.promotion_code ? `(${orderState.promotion_code})` : '' }}:</span>
                <span class="font-semibold">-KES {{ summaryState.discount.toFixed(2) }}</span>
              </div>
              
              <!-- VAT -->
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">VAT (16%):</span>
                <span class="font-semibold">KES {{ summaryState.vat.toFixed(2) }}</span>
              </div>
              
              <!-- Shipping -->
              <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Shipping:</span>
                <span class="font-semibold" :class="{ 'text-green-600': summaryState.shipping === 0 }">
                  {{ summaryState.shipping === 0 ? 'FREE' : `KES ${summaryState.shipping.toFixed(2)}` }}
                </span>
              </div>
              
              <!-- Total -->
              <div class="border-t dark:border-zinc-700 pt-3 flex justify-between font-bold text-lg text-yellow-600">
                <span>Total:</span>
                <span>KES {{ summaryState.grandTotal.toFixed(2) }}</span>
              </div>
            </div>
          </div>

          <!-- Promo Code Used -->
          <div v-if="orderState.promotion_code" class="mt-6 bg-green-50 dark:bg-green-900/20 p-4 rounded border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">🎉</span>
              <div>
                <p class="text-green-700 dark:text-green-400 font-semibold">
                  Promo code applied: {{ orderState.promotion_code }}
                </p>
                <p class="text-sm text-green-600 dark:text-green-500">
                  You saved KES {{ summaryState.discount.toFixed(2) }} on this order!
                </p>
              </div>
            </div>
          </div>

          <!-- Payment Info -->
          <div v-if="orderState.payment_status === 'unpaid'" class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded border border-yellow-200 dark:border-yellow-800">
            <div class="flex items-center gap-2">
              <span class="text-2xl">💳</span>
              <div>
                <p class="text-yellow-700 dark:text-yellow-400 font-semibold">
                  Payment Pending
                </p>
                <p class="text-sm text-yellow-600 dark:text-yellow-500">
                  Please complete payment to confirm your order
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button @click="viewOrder" class="px-6 py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-bold rounded-lg">
            View Order Details
          </button>
          <button @click="continueShopping" class="px-6 py-3 bg-gray-200 dark:bg-zinc-700 hover:bg-gray-300 dark:hover:bg-zinc-600 font-bold rounded-lg">
            Continue Shopping
          </button>
        </div>
      </div>
    </ProfileLayout>
  </MainLayout>
</template>
