<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

// Props from backend
const props = defineProps({
  order: { type: Object, required: true },
  summary: { type: Object, required: true },
  invoice: { type: Object, default: null },
});

// Reactive state
const orderState = ref({ ...props.order });
const summaryState = ref({ ...props.summary });
const showPaymentModal = ref(false);
const isProcessing = ref(false);
let pollInterval = null;

// Get refunds from order
const refunds = computed(() => orderState.value.refunds || []);
const hasRefunds = computed(() => refunds.value.length > 0);
const totalRefunded = computed(() => {
  return refunds.value
    .filter(r => r.status === 'completed')
    .reduce((sum, r) => sum + parseFloat(r.amount), 0);
});

// ----- Polling for payment status -----
async function pollPaymentStatus() {
  if (!orderState.value.id) return;

  try {
    const res = await axios.get(`/orders/${orderState.value.id}/status`);
    orderState.value.payment_status = res.data.payment_status;
    orderState.value.status = res.data.status;

    if (orderState.value.payment_status?.toLowerCase() === 'paid') {
      clearInterval(pollInterval);
      toast.success(`Order #${orderState.value.order_number} is now paid!`);
    }
  } catch (err) {
    console.error('Failed to fetch latest order status', err);
  }
}

// Start polling
onMounted(() => {
  if (orderState.value.payment_status?.toLowerCase() === 'pending') {
    pollInterval = setInterval(pollPaymentStatus, 5000);
  }
});

// Stop polling on unmounted
onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval);
});

// ----- Payment flow -----
function openPaymentModal() {
  showPaymentModal.value = true;
}

async function confirmPayment() {
  if (!orderState.value.id) return;

  const phone = prompt('Enter your phone number (format: 2547XXXXXXXX):', orderState.value.user_phone || '');
  if (!phone) return;

  isProcessing.value = true;
  try {
    const res = await axios.post(`/payments/${orderState.value.id}/mpesa`, { phone });
    toast.success(res.data.message || 'Payment prompt sent!');
    orderState.value.payment_status = 'pending';
    showPaymentModal.value = false;

    // Start polling
    pollInterval = setInterval(pollPaymentStatus, 5000);
  } catch (err) {
    toast.error(err.response?.data?.message || err.message);
  } finally {
    isProcessing.value = false;
  }
}

// ----- Format date -----
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// ----- Get status color -----
function getRefundStatusColor(status) {
  const colors = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return colors[status] || 'bg-gray-100 text-gray-800';
}

// ----- Get status label -----
function getRefundStatusLabel(status) {
  const labels = {
    pending: 'Pending',
    processing: 'Processing',
    completed: 'Completed',
    failed: 'Failed',
    rejected: 'Rejected',
  };
  return labels[status] || status;
}

// ----- Financial calculations (computed) -----
const itemsTotal = computed(() => {
  if (!orderState.value.order_items) return 0;
  return orderState.value.order_items.reduce((sum, item) => sum + item.price * item.quantity, 0);
});
const taxRate = 0.16;
const taxAmount = computed(() => itemsTotal.value * taxRate);
const shippingFee = computed(() => orderState.value.shipping_fee || 150);
const discountAmount = computed(() => orderState.value.discount || 0);
const grandTotal = computed(() => itemsTotal.value + taxAmount.value + shippingFee.value - discountAmount.value);
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="Order Details" />

      <div class="container mx-auto p-4 space-y-4">

        <!-- Back Button -->
        <button @click="router.visit(route('orders.index'))"
                class="px-4 py-2 bg-gray-200 dark:bg-zinc-700 rounded hover:bg-gray-300">
          ← Back to My Orders
        </button>

        <!-- Order Header -->
        <h1 class="text-2xl font-bold">Order #{{ orderState.order_number }}</h1>
        <div class="text-gray-600 mb-4">
          Placed on: {{ formatDate(orderState.created_at) }} <br>
          Status: <span class="font-semibold">{{ orderState.status }}</span> |
          Payment: <span class="font-semibold">{{ orderState.payment_status }}</span>
        </div>

        <!-- Refunds Section - With Refunds -->
        <div v-if="hasRefunds" class="bg-white dark:bg-zinc-800 p-4 rounded shadow">
          <h2 class="font-bold text-lg mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
            </svg>
            Refund Information
          </h2>
          
          <div class="space-y-3">
            <div v-for="refund in refunds" :key="refund.id" 
                 class="border rounded-lg p-3 flex justify-between items-center">
              <div>
                <div class="font-semibold">
                  {{ refund.refund_type === 'full' ? 'Full Refund' : 'Partial Refund' }}
                </div>
                <div class="text-sm text-gray-500">
                  Amount: KES {{ parseFloat(refund.amount).toFixed(2) }}
                </div>
                <div v-if="refund.reason" class="text-sm text-gray-500">
                  Reason: {{ refund.reason }}
                </div>
                <div class="text-xs text-gray-400">
                  {{ formatDate(refund.created_at) }}
                </div>
              </div>
              <div :class="getRefundStatusColor(refund.status)" 
                   class="px-3 py-1 rounded-full text-sm font-medium">
                {{ getRefundStatusLabel(refund.status) }}
              </div>
            </div>
          </div>

          <div v-if="totalRefunded > 0" class="mt-3 pt-3 border-t">
            <div class="flex justify-between font-bold text-green-600">
              <span>Total Refunded:</span>
              <span>KES {{ totalRefunded.toFixed(2) }}</span>
            </div>
          </div>
        </div>

        <!-- No Refunds Message -->
        <div v-else class="bg-white dark:bg-zinc-800 p-4 rounded shadow">
          <h2 class="font-bold text-lg mb-3 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Refund Information
          </h2>
          <p class="text-gray-500">No refunds have been issued for this order.</p>
          <p v-if="orderState.payment_status === 'paid'" class="text-sm text-gray-400 mt-1">
            If you believe you're owed a refund, please contact support.
          </p>
        </div>

        <!-- Items Table -->
        <div class="bg-white dark:bg-zinc-800 p-4 rounded shadow space-y-2">
          <h2 class="font-bold text-lg mb-2">Order Items</h2>
          <div v-for="item in orderState.order_items" :key="item.id"
               class="flex justify-between items-center border-b last:border-b-0 pb-2">
            <div class="flex items-center gap-3">
              <img
                :src="item.product?.thumbnail_url || '/images/placeholder.svg'"
                alt="Product Image"
                class="w-20 h-20 object-cover rounded-md"
              />
              <div>
                <div class="font-semibold">{{ item.product_name }}</div>
                <div class="text-gray-500 text-sm">Quantity: {{ item.quantity }}</div>
              </div>
            </div>
            <div class="font-bold text-yellow-600">
              KES {{ (item.price * item.quantity).toFixed(2) }}
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="mt-4 bg-white dark:bg-zinc-800 p-4 rounded shadow max-w-md ml-auto space-y-1">
          <h2 class="font-bold text-lg mb-2">Order Summary</h2>
          <div class="flex justify-between">
            <span>Items Total:</span>
            <span>KES {{ itemsTotal.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span>VAT (16%):</span>
            <span>KES {{ taxAmount.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between">
            <span>Shipping Fee:</span>
            <span>KES {{ shippingFee.toFixed(2) }}</span>
          </div>
          <div class="flex justify-between text-red-600">
            <span>Discount:</span>
            <span>- KES {{ discountAmount.toFixed(2) }}</span>
          </div>
          <div class="border-t mt-2 pt-2 flex justify-between font-bold text-yellow-600 text-lg">
            <span>Grand Total:</span>
            <span>KES {{ grandTotal.toFixed(2) }}</span>
          </div>
        </div>

        <!-- Pay Now button -->
        <div v-if="orderState.payment_status?.toLowerCase() === 'unpaid'" class="mt-4">
          <button @click="openPaymentModal"
                  class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded"
                  :disabled="isProcessing">
            {{ isProcessing ? 'Processing...' : 'Pay Now' }}
          </button>
        </div>

        <!-- Invoice Button -->
        <div v-if="invoice" class="mt-4">
          <a :href="route('orders.invoice.download', orderState.id)" 
             target="_blank"
             class="inline-flex items-center px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download Invoice
          </a>
        </div>

        <!-- Payment Modal -->
        <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
          <div class="bg-white dark:bg-zinc-800 rounded shadow-lg p-6 w-11/12 max-w-md">
            <h2 class="text-xl font-bold mb-4">Confirm Payment</h2>
            <p class="mb-4">
              Proceed to pay <strong>KES {{ grandTotal.toFixed(2) }}</strong> for order
              <strong>#{{ orderState.order_number }}</strong>?
            </p>
            <div class="flex justify-end space-x-2">
              <button @click="showPaymentModal = false"
                      class="px-4 py-2 bg-gray-200 dark:bg-zinc-700 rounded hover:bg-gray-300">
                Cancel
              </button>
              <button @click="confirmPayment"
                      class="px-4 py-2 bg-yellow-400 hover:bg-yellow-500 text-black font-bold rounded"
                      :disabled="isProcessing">
                {{ isProcessing ? 'Sending Prompt...' : 'Pay Now' }}
              </button>
            </div>
          </div>
        </div>

      </div>
    </ProfileLayout>
  </MainLayout>
</template>
