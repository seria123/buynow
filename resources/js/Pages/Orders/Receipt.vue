<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

const props = defineProps({
  order: { type: Object, required: true },
  invoice: { type: Object, required: true },
});

// Format date
function formatDate(dateString) {
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
}

// Format currency
function formatCurrency(amount) {
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES'
  }).format(amount);
}

// Download receipt PDF
function downloadReceipt() {
  window.open(route('orders.receipt.download', props.order.id), '_blank');
}
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="Payment Receipt" />

      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <div>
            <h1 class="text-2xl font-bold text-green-600">Payment Receipt</h1>
            <p class="text-gray-600 mt-1">Proof of Payment</p>
          </div>
          <button
            @click="downloadReceipt"
            class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 flex items-center gap-2 font-bold shadow-lg"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Download Receipt PDF
          </button>
        </div>

        <!-- Paid Status Badge -->
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
          <div class="flex items-center gap-3">
            <div class="bg-green-100 rounded-full p-2">
              <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <p class="font-bold text-green-800">✓ PAID</p>
              <p class="text-sm text-green-600">Payment has been successfully processed</p>
            </div>
          </div>
        </div>

        <!-- Receipt Details Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Receipt Information</h3>
              <p class="text-lg font-bold">{{ invoice.invoice_number }}</p>
              <p class="text-gray-600">Order: {{ order.order_number }}</p>
              <p class="text-gray-600">Date: {{ formatDate(invoice.invoice_date) }}</p>
              <p class="text-gray-600">Payment Method: {{ order.payment_method || 'M-Pesa' }}</p>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Customer Information</h3>
              <p class="font-medium">{{ order.user?.name }}</p>
              <p class="text-gray-600">{{ order.user?.email }}</p>
              <p v-if="order.user?.phone" class="text-gray-600">{{ order.user?.phone }}</p>
            </div>
          </div>
        </div>

        <!-- Transaction Details -->
        <div v-if="order.transactions && order.transactions.length > 0" class="bg-white rounded-lg shadow p-6 mb-6">
          <h3 class="text-sm font-semibold text-gray-500 uppercase mb-4">Payment Transaction Details</h3>
          <div v-for="txn in order.transactions.filter(t => t.status === 'completed')" :key="txn.id" class="border rounded-lg p-4 mb-3 bg-gray-50">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-sm text-gray-500">Transaction ID</p>
                <p class="font-medium">{{ txn.transaction_number || 'N/A' }}</p>
              </div>
              <div v-if="txn.mpesa_transaction_id">
                <p class="text-sm text-gray-500">M-Pesa Receipt</p>
                <p class="font-medium">{{ txn.mpesa_transaction_id }}</p>
              </div>
              <div v-if="txn.mpesa_phone_number">
                <p class="text-sm text-gray-500">Phone Number</p>
                <p class="font-medium">{{ txn.mpesa_phone_number }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Amount Paid</p>
                <p class="font-medium text-green-600">{{ formatCurrency(txn.amount) }}</p>
              </div>
              <div>
                <p class="text-sm text-gray-500">Payment Time</p>
                <p class="font-medium">{{ formatDate(txn.processed_at || txn.created_at) }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-green-600">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">Item</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-white uppercase">SKU</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Price</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-white uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in order.order_items" :key="item.id">
                <td class="px-6 py-4">
                  <p class="font-medium">{{ item.product_name }}</p>
                  <p v-if="item.variant_name" class="text-sm text-gray-500">{{ item.variant_name }}</p>
                </td>
                <td class="px-6 py-4 text-gray-600">{{ item.product?.sku || 'N/A' }}</td>
                <td class="px-6 py-4 text-right">{{ formatCurrency(item.price) }}</td>
                <td class="px-6 py-4 text-right">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-right font-medium">{{ formatCurrency(item.price * item.quantity) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
          <div class="w-80 bg-white rounded-lg shadow p-6">
            <div class="flex justify-between py-2">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
            </div>
            <div class="flex justify-between py-2" v-if="invoice.tax_amount > 0">
              <span class="text-gray-600">Tax (VAT)</span>
              <span class="font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
            </div>
            <div class="flex justify-between py-2" v-if="order.shipping_cost > 0">
              <span class="text-gray-600">Shipping</span>
              <span class="font-medium">{{ formatCurrency(order.shipping_cost) }}</span>
            </div>
            <div class="flex justify-between py-2 text-red-600" v-if="order.discount_amount > 0">
              <span>Discount</span>
              <span class="font-medium">- {{ formatCurrency(order.discount_amount) }}</span>
            </div>
            <div class="flex justify-between py-3 border-t-2 border-green-600 mt-3">
              <span class="text-lg font-bold text-green-600">Total Paid</span>
              <span class="text-lg font-bold text-green-600">{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="invoice.notes" class="mt-6 bg-green-50 rounded-lg p-4 border-l-4 border-green-600">
          <h4 class="font-semibold mb-2 text-green-800">Notes</h4>
          <p class="text-gray-700">{{ invoice.notes }}</p>
        </div>

        <!-- Footer Message -->
        <div class="mt-8 text-center text-gray-600 border-t pt-6">
          <p class="font-semibold text-green-600 mb-2">Thank you for your purchase!</p>
          <p class="text-sm">This receipt confirms that your payment has been successfully processed.</p>
          <p class="text-sm">For any inquiries, please contact our support team.</p>
        </div>
      </div>
    </ProfileLayout>
  </MainLayout>
</template>
