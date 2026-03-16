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

// Download invoice PDF
function downloadInvoice() {
  window.open(route('orders.invoice.download', props.order.id), '_blank');
}
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <Head title="Invoice" />

      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
          <h1 class="text-2xl font-bold">Invoice</h1>
          <button
            @click="downloadInvoice"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 flex items-center gap-2"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Download PDF
          </button>
        </div>

        <!-- Invoice Details Card -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
          <div class="grid grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Invoice Info</h3>
              <p class="text-lg font-bold">{{ invoice.invoice_number }}</p>
              <p class="text-gray-600">Order: {{ order.order_number }}</p>
              <p class="text-gray-600">Date: {{ formatDate(invoice.invoice_date) }}</p>
              <div class="mt-2">
                <span 
                  :class="{
                    'bg-green-100 text-green-800': invoice.status === 'paid',
                    'bg-yellow-100 text-yellow-800': invoice.status === 'pending',
                    'bg-red-100 text-red-800': invoice.status === 'cancelled',
                  }"
                  class="px-3 py-1 rounded-full text-sm font-medium"
                >
                  {{ invoice.status.toUpperCase() }}
                </span>
              </div>
            </div>
            <div>
              <h3 class="text-sm font-semibold text-gray-500 uppercase mb-2">Bill To</h3>
              <p class="font-medium">{{ order.user?.name }}</p>
              <p class="text-gray-600">{{ order.user?.email }}</p>
            </div>
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Price</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Qty</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr v-for="item in order.order_items" :key="item.id">
                <td class="px-6 py-4">
                  <p class="font-medium">{{ item.product_name }}</p>
                  <p v-if="item.variant" class="text-sm text-gray-500">{{ item.variant.name }}</p>
                </td>
                <td class="px-6 py-4 text-right">{{ formatCurrency(item.price) }}</td>
                <td class="px-6 py-4 text-right">{{ item.quantity }}</td>
                <td class="px-6 py-4 text-right font-medium">{{ formatCurrency(item.price * item.quantity) }}</td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Totals -->
        <div class="flex justify-end">
          <div class="w-64">
            <div class="flex justify-between py-2">
              <span class="text-gray-600">Subtotal</span>
              <span class="font-medium">{{ formatCurrency(invoice.subtotal) }}</span>
            </div>
            <div class="flex justify-between py-2" v-if="invoice.tax_amount > 0">
              <span class="text-gray-600">Tax</span>
              <span class="font-medium">{{ formatCurrency(invoice.tax_amount) }}</span>
            </div>
            <div class="flex justify-between py-3 border-t border-gray-300">
              <span class="text-lg font-bold">Total</span>
              <span class="text-lg font-bold">{{ formatCurrency(invoice.total_amount) }}</span>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div v-if="invoice.notes" class="mt-6 bg-gray-50 rounded-lg p-4">
          <h4 class="font-semibold mb-2">Notes</h4>
          <p class="text-gray-600">{{ invoice.notes }}</p>
        </div>
      </div>
    </ProfileLayout>
  </MainLayout>
</template>
