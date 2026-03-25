<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

const props = defineProps({
    warranty: Object,
});

// Get status color class
function getStatusColor(status) {
    switch (status) {
        case 'Active':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'Expired':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'Claimed':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'Cancelled':
            return 'bg-gray-100 text-gray-800 border-gray-200';
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}

// Get type badge color
function getTypeColor(type) {
    switch (type) {
        case 'standard':
            return 'bg-blue-100 text-blue-800';
        case 'extended':
            return 'bg-orange-100 text-orange-800';
        case 'lifetime':
            return 'bg-purple-100 text-purple-800';
        case 'manufacturer':
            return 'bg-indigo-100 text-indigo-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}

// Format date
function formatDate(date) {
    if (!date) return 'N/A';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<template>
    <MainLayout>
        <ProfileLayout>
            <Head title="Warranty Details" />

            <div class="container mx-auto p-4">
                <!-- Back button -->
                <a
                    href="/profile/warranties"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 mb-4"
                >
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                    Back to Warranties
                </a>

                <div class="bg-white border rounded-lg shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gray-50 p-6 border-b">
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <div>
                                <h1 class="text-2xl font-bold">Warranty Details</h1>
                                <p class="text-gray-600">Warranty #{{ props.warranty.warranty_number }}</p>
                            </div>
                            <div class="flex gap-3">
                                <span :class="['px-3 py-1 rounded-full text-sm font-medium', getTypeColor(props.warranty.warranty_type)]">
                                    {{ props.warranty.warranty_type }} Warranty
                                </span>
                                <span :class="['px-3 py-1 rounded-full text-sm font-medium border', getStatusColor(props.warranty.status_label)]">
                                    {{ props.warranty.status_label }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="p-6">
                        <!-- Product Info -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                                </svg>
                                Product Information
                            </h2>
                            <div class="flex items-center gap-4 bg-gray-50 p-4 rounded-lg">
                                <img
                                    :src="props.warranty.product?.thumbnail_url || '/images/placeholder.svg'"
                                    :alt="props.warranty.product?.name"
                                    class="w-24 h-24 object-cover rounded-lg bg-white"
                                />
                                <div>
                                    <h3 class="text-xl font-semibold">{{ props.warranty.product?.name || 'Product' }}</h3>
                                    <p class="text-gray-600">SKU: {{ props.warranty.product?.sku || 'N/A' }}</p>
                                    <a
                                        v-if="props.warranty.product"
                                        :href="`/product/${props.warranty.product.slug}`"
                                        class="text-blue-600 hover:underline text-sm"
                                    >
                                        View Product
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Warranty Period -->
                        <div class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                Warranty Period
                            </h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">Start Date</p>
                                    <p class="text-lg font-medium">{{ formatDate(props.warranty.start_date) }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">End Date</p>
                                    <p class="text-lg font-medium">{{ formatDate(props.warranty.end_date) }}</p>
                                </div>
                                <div class="bg-gray-50 p-4 rounded-lg">
                                    <p class="text-sm text-gray-500">Remaining Time</p>
                                    <p class="text-lg font-medium" :class="props.warranty.remaining_days <= 30 ? 'text-red-600' : props.warranty.remaining_days <= 90 ? 'text-yellow-600' : 'text-green-600'">
                                        {{ props.warranty.remaining_days }} days
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Order Info -->
                        <div v-if="props.warranty.order" class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                                </svg>
                                Order Information
                            </h2>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="text-sm text-gray-500">Order Number</p>
                                <a :href="`/profile/orders/${props.warranty.order.id}`" class="text-lg font-medium text-blue-600 hover:underline">
                                    {{ props.warranty.order.order_number }}
                                </a>
                            </div>
                        </div>

                        <!-- Terms & Conditions -->
                        <div v-if="props.warranty.terms" class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Terms & Conditions
                            </h2>
                            <div class="bg-gray-50 p-4 rounded-lg">
                                <p class="whitespace-pre-wrap">{{ props.warranty.terms }}</p>
                            </div>
                        </div>

                        <!-- Claim Instructions -->
                        <div v-if="props.warranty.claim_instructions" class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                How to Claim
                            </h2>
                            <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                                <p class="whitespace-pre-wrap">{{ props.warranty.claim_instructions }}</p>
                            </div>
                        </div>

                        <!-- Warranty Document -->
                        <div v-if="props.warranty.document_path" class="mb-8">
                            <h2 class="text-lg font-semibold mb-4 flex items-center">
                                <svg class="w-5 h-5 mr-2 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                Warranty Document
                            </h2>
                            <a
                                :href="`/storage/${props.warranty.document_path}`"
                                target="_blank"
                                class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                                </svg>
                                View Document
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </ProfileLayout>
    </MainLayout>
</template>