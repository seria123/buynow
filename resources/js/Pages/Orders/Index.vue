<script setup>
import { reactive, ref, onMounted, onUnmounted } from 'vue';
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

// Props from backend
const props = defineProps({ orders: Array });

// Track expanded/collapsed state
const expanded = reactive({});
const pollingIntervals = reactive({}); // polling timers per order

// Track per-order phone input & messages
const phoneInputs = reactive({});
const messages = reactive({});
const messageTypes = reactive({});
const loadingStates = reactive({});

// Initialize states
onMounted(() => {
  (props.orders || []).forEach(order => {
    expanded[order.id] = false;
    phoneInputs[order.id] = '';
    messages[order.id] = '';
    messageTypes[order.id] = 'text-green-600';
    loadingStates[order.id] = false;
  });
});

// Cleanup polling intervals
onUnmounted(() => {
  Object.values(pollingIntervals).forEach(clearInterval);
});

// Toggle order items
function toggleOrder(id) {
  if (!id) return;
  expanded[id] = !expanded[id];
}

// Start polling payment status
function startPolling(order) {
  if (!order?.id || pollingIntervals[order.id]) return;

  pollingIntervals[order.id] = setInterval(async () => {
    try {
      const res = await axios.get(`/orders/${order.id}/status`);
      order.payment_status = res.data.payment_status;
      order.status = res.data.status;

      const paymentStatus = order.payment_status?.toLowerCase();
      
      // Stop polling when payment is no longer pending
      if (paymentStatus === 'paid' || paymentStatus === 'cancelled' || paymentStatus === 'failed' || paymentStatus === 'expired') {
        clearInterval(pollingIntervals[order.id]);
        delete pollingIntervals[order.id];
        
        if (paymentStatus === 'paid') {
          toast.success(`Order #${order.order_number} is now paid!`);
        } else if (paymentStatus === 'cancelled') {
          toast.info(`Payment for order #${order.order_number} was cancelled.`);
        } else if (paymentStatus === 'failed') {
          toast.error(`Payment for order #${order.order_number} failed.`);
        }
      }
    } catch (err) {
      console.error('Polling failed', err);
    }
  }, 5000);
}

// Pay order (inline)
async function payOrder(order) {
  if (!order?.id) return;

  const phone = phoneInputs[order.id];
  if (!phone || phone.length !== 12) {
    messages[order.id] = 'Enter a valid phone number (2547XXXXXXXX)';
    messageTypes[order.id] = 'text-red-600';
    return;
  }

  loadingStates[order.id] = true;
  messages[order.id] = '';

  try {
    const res = await axios.post(`/payments/${order.id}/mpesa`, { phone });
    console.log('STK Push Response:', res.data.stk_response);

    messages[order.id] = 'STK Push sent! Check your phone.';
    messageTypes[order.id] = 'text-green-600';
    order.payment_status = 'pending';

    startPolling(order);
  } catch (err) {
    console.error('STK Push failed:', err.response?.data || err.message);
    messages[order.id] = err.response?.data?.error || 'Payment failed. Check console.';
    messageTypes[order.id] = 'text-red-600';
  } finally {
    loadingStates[order.id] = false;
  }
}

// Delete order
async function deleteOrder(order) {
  if (!order?.id || !confirm('Delete this order?')) return;
  try {
    await axios.delete(`/orders/${order.id}`);
    const index = props.orders.findIndex(o => o.id === order.id);
    if (index !== -1) props.orders.splice(index, 1);
    delete expanded[order.id];
    toast.success('Order deleted');
  } catch (err) {
    toast.error(err.response?.data?.message || err.message);
  }
}

// Request return for a delivered order
async function requestReturn(order) {
  if (!order?.id) return;
  if (!confirm(`Request a return for Order #${order.order_number}?`)) return;
  try {
    const res = await axios.post(`/orders/${order.id}/return-request`);
    order.return_status = 'requested';
    toast.success(res.data.message);
  } catch (err) {
    toast.error(err.response?.data?.message || err.message);
  }
}

// Get refund status class
function getRefundStatusClass(refunds) {
  if (!refunds || refunds.length === 0) return '';
  const hasCompleted = refunds.some(r => r.status === 'completed');
  const hasPending = refunds.some(r => r.status === 'pending' || r.status === 'processing');
  const hasFailed = refunds.some(r => r.status === 'failed' || r.status === 'rejected');
  
  if (hasFailed) return 'text-red-600 font-semibold';
  if (hasCompleted) return 'text-green-600 font-semibold';
  if (hasPending) return 'text-yellow-600 font-semibold';
  return '';
}

// Get refund status text
function getRefundStatusText(refunds) {
  if (!refunds || refunds.length === 0) return '';
  const hasCompleted = refunds.some(r => r.status === 'completed');
  const hasPending = refunds.some(r => r.status === 'pending' || r.status === 'processing');
  const hasFailed = refunds.some(r => r.status === 'failed' || r.status === 'rejected');
  
  if (hasFailed) return 'Failed/Rejected';
  if (hasCompleted) return 'Refunded';
  if (hasPending) return 'In Progress';
  return '';
}

// Get total refunded amount
function getTotalRefunded(refunds) {
  if (!refunds || refunds.length === 0) return 0;
  return refunds
    .filter(r => r.status === 'completed')
    .reduce((sum, r) => sum + parseFloat(r.amount), 0);
}
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="My Orders" />

      <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">My Orders</h1>

        <div v-if="!(props.orders || []).length" class="text-gray-500">
          No orders yet.
        </div>

        <div v-else class="space-y-4">
          <div
            v-for="order in props.orders"
            :key="order.id"
            class="p-4 border rounded shadow"
          >
            <!-- Order header -->
            <div class="flex justify-between items-center mb-2">
              <div>
                <div class="font-semibold text-lg">Order #{{ order.order_number }}</div>
                <div class="text-gray-500 text-sm">
                  Status: <span>{{ order.status }}</span> |
                  Payment: <span :class="{
                    'text-green-600 font-semibold': order.payment_status === 'paid',
                    'text-red-600 font-semibold': order.payment_status === 'cancelled' || order.payment_status === 'failed',
                    'text-yellow-600 font-semibold': order.payment_status === 'pending' || order.payment_status === 'unpaid',
                  }">{{ order.payment_status }}</span>
                  <span v-if="order.refunds && order.refunds.length > 0"> |
                    Refund: <span :class="getRefundStatusClass(order.refunds)">{{ getRefundStatusText(order.refunds) }}</span>
                  </span>
                </div>
                <div>Total: KES {{ order.total_amount }}</div>
                <div v-if="getTotalRefunded(order.refunds) > 0" class="text-green-600 text-sm">
                  Refunded: KES {{ getTotalRefunded(order.refunds).toFixed(2) }}
                </div>
                <div v-if="order.transactions && order.transactions.length > 0" class="text-sm text-gray-500 mt-1">
                  <span>Transaction: {{ order.transactions[0].transaction_number }}</span>
                  <span v-if="order.transactions[0].mpesa_transaction_id" class="ml-2">
                    (M-Pesa: {{ order.transactions[0].mpesa_transaction_id }})
                  </span>
                </div>
              </div>

              <!-- Action buttons -->
              <div class="flex space-x-2">
                <button
                  v-if="order.payment_status === 'unpaid'"
                  @click="payOrder(order)"
                  :disabled="loadingStates[order.id]"
                  class="bg-yellow-400 hover:bg-yellow-500 text-black font-bold px-3 py-1 rounded"
                >
                  {{ loadingStates[order.id] ? 'Processing...' : 'Pay Now' }}
                </button>

                <button
                  @click="toggleOrder(order.id)"
                  class="bg-gray-200 px-3 py-1 rounded"
                >
                  {{ expanded[order.id] ? 'Hide Items' : 'View Items' }}
                </button>

                <button
                  @click="deleteOrder(order)"
                  class="bg-red-600 text-white px-3 py-1 rounded"
                >
                  Delete
                </button>

                <!-- Request Return (only for delivered orders) -->
                <template v-if="order.status === 'delivered'">
                  <button
                    v-if="!order.return_status"
                    @click="requestReturn(order)"
                    class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700"
                  >
                    Request Return
                  </button>
                  <span
                    v-else
                    :class="{
                      'bg-yellow-100 text-yellow-800': order.return_status === 'requested',
                      'bg-green-100 text-green-800': order.return_status === 'approved',
                      'bg-red-100 text-red-800': order.return_status === 'rejected',
                    }"
                    class="px-3 py-1 rounded text-sm font-semibold capitalize"
                  >
                    Return {{ order.return_status }}
                  </span>
                </template>
              </div>
            </div>

            <!-- Inline phone input & messages for STK push -->
            <div v-if="order.payment_status === 'unpaid'" class="mt-2">
              <input
                v-model="phoneInputs[order.id]"
                type="text"
                placeholder="2547XXXXXXXX"
                class="w-full border p-2 rounded mb-2"
              />
              <div v-if="messages[order.id]" :class="messageTypes[order.id]" class="p-2 rounded mb-2">
                {{ messages[order.id] }}
              </div>
            </div>

            <!-- Order items -->
            <div v-if="expanded[order.id]" class="mt-2 p-2 bg-gray-50 rounded">
              <div
                v-for="item in order.order_items"
                :key="item.id"
                class="flex justify-between border-b last:border-b-0 py-1 items-center"
              >
                <img
                  :src="item.product?.thumbnail_url || '/images/placeholder.svg'"
                  alt="Product Image"
                  class="w-20 h-20 object-cover rounded-md"
                />
                <div>{{ item.product_name }} × {{ item.quantity }}</div>
                <div>KES {{ (item.price * item.quantity).toFixed(2) }}</div>
              </div>

              <div v-if="!order.order_items || order.order_items.length === 0"
                   class="text-gray-500">
                No items found.
              </div>
            </div>
          </div>
        </div>
      </div>
    </ProfileLayout>
  </MainLayout>
</template>