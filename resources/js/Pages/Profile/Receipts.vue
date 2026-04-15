<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import axios from 'axios';
import { toast } from 'vue3-toastify';

const orderNumber = ref('');
const isLoading = ref(false);
const receiptUrl = ref(null);
const orderDetails = ref(null);

async function generateReceipt() {
    if (!orderNumber.value.trim()) {
        toast.error('Please enter an order number');
        return;
    }

    isLoading.value = true;
    receiptUrl.value = null;
    orderDetails.value = null;

    try {
        const res = await axios.post('/api/receipts/generate', {
            order_number: orderNumber.value.trim()
        });

        if (res.data.success) {
            receiptUrl.value = res.data.receipt_url;
            orderDetails.value = res.data.order;
            toast.success('Receipt generated successfully!');
        } else {
            toast.error(res.data.message || 'Failed to generate receipt');
        }
    } catch (err) {
        const errorMessage = err.response?.data?.message || err.message;
        toast.error(errorMessage);
    } finally {
        isLoading.value = false;
    }
}

function downloadReceipt() {
    if (receiptUrl.value) {
        window.open(receiptUrl.value, '_blank');
    }
}

function clearForm() {
    orderNumber.value = '';
    receiptUrl.value = null;
    orderDetails.value = null;
}
</script>

<template>
    <MainLayout>
        <ProfileLayout>
            <Head title="Receipts" />

            <div class="space-y-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Receipts</h1>
                    <p class="text-gray-500 dark:text-gray-400 mt-1">Generate and download receipts for your orders</p>
                </div>

                <!-- Generate Receipt Form -->
                <div class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Generate Receipt</h2>
                    
                    <form @submit.prevent="generateReceipt" class="space-y-4">
                        <div>
                            <label for="orderNumber" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Order Number
                            </label>
                            <input
                                id="orderNumber"
                                v-model="orderNumber"
                                type="text"
                                placeholder="Enter order number (e.g., ORD-20260327-ABCD)"
                                class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-lg focus:ring-2 focus:ring-yellow-400 focus:border-transparent dark:bg-zinc-700 dark:text-white"
                                :disabled="isLoading"
                            />
                        </div>

                        <div class="flex gap-3">
                            <button
                                type="submit"
                                :disabled="isLoading || !orderNumber.trim()"
                                class="px-6 py-2 bg-yellow-400 hover:bg-yellow-500 disabled:bg-gray-300 disabled:cursor-not-allowed text-black font-semibold rounded-lg transition-colors"
                            >
                                <span v-if="isLoading">Generating...</span>
                                <span v-else>Generate Receipt</span>
                            </button>

                            <button
                                v-if="receiptUrl || orderDetails"
                                type="button"
                                @click="clearForm"
                                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 dark:bg-zinc-700 dark:hover:bg-zinc-600 text-gray-700 dark:text-gray-300 font-semibold rounded-lg transition-colors"
                            >
                                Clear
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Receipt Result -->
                <div v-if="receiptUrl || orderDetails" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Receipt Generated</h2>
                    
                    <!-- Order Details -->
                    <div v-if="orderDetails" class="mb-6 p-4 bg-gray-50 dark:bg-zinc-700 rounded-lg">
                        <h3 class="font-medium text-gray-900 dark:text-white mb-3">Order Details</h3>
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Order Number:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium">{{ orderDetails.order_number }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Status:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium capitalize">{{ orderDetails.status }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Payment Status:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium capitalize">{{ orderDetails.payment_status }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 dark:text-gray-400">Total Amount:</span>
                                <span class="ml-2 text-gray-900 dark:text-white font-medium">KES {{ parseFloat(orderDetails.total_amount).toFixed(2) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Download Button -->
                    <div v-if="receiptUrl" class="flex items-center gap-4">
                        <a
                            :href="receiptUrl"
                            target="_blank"
                            class="inline-flex items-center px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download Receipt PDF
                        </a>
                        <span class="text-sm text-gray-500 dark:text-gray-400">
                            Receipt is ready for download
                        </span>
                    </div>
                </div>

                <!-- Instructions -->
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h3 class="font-medium text-blue-800 dark:text-blue-300 mb-2">How to generate a receipt</h3>
                    <ul class="text-sm text-blue-700 dark:text-blue-400 space-y-1 list-disc list-inside">
                        <li>Enter your order number in the field above</li>
                        <li>Click "Generate Receipt" to create a PDF receipt</li>
                        <li>Download the receipt for your records</li>
                        <li>Receipts are only available for paid orders</li>
                    </ul>
                </div>
            </div>
        </ProfileLayout>
    </MainLayout>
</template>
