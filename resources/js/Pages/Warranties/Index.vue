<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

const props = defineProps({
    warranties: Array,
});

// Get status color class
function getStatusColor(status) {
    switch (status) {
        case 'Active':
            return 'bg-green-100 text-green-800';
        case 'Expired':
            return 'bg-red-100 text-red-800';
        case 'Claimed':
            return 'bg-yellow-100 text-yellow-800';
        case 'Cancelled':
            return 'bg-gray-100 text-gray-800';
        default:
            return 'bg-gray-100 text-gray-800';
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
        month: 'short',
        day: 'numeric'
    });
}
</script>

<template>
    <MainLayout>
        <ProfileLayout>
            <Head title="My Warranties" />

            <div class="container mx-auto p-4">
                <h1 class="text-2xl font-bold mb-6">My Warranties</h1>

                <div v-if="!(props.warranties || []).length" class="text-gray-500 text-center py-12">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                    </svg>
                    <p class="text-lg">No warranties yet</p>
                    <p class="text-sm">Your product warranties will appear here</p>
                </div>

                <div v-else class="space-y-4">
                    <div
                        v-for="warranty in props.warranties"
                        :key="warranty.id"
                        class="bg-white border rounded-lg shadow-sm hover:shadow-md transition-shadow p-4"
                    >
                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                            <!-- Product Info -->
                            <div class="flex items-center gap-4">
                                <img
                                    :src="warranty.product?.thumbnail_url || '/images/placeholder.svg'"
                                    :alt="warranty.product?.name"
                                    class="w-20 h-20 object-cover rounded-lg bg-gray-100"
                                />
                                <div>
                                    <h3 class="font-semibold text-lg">{{ warranty.product?.name || 'Product' }}</h3>
                                    <p class="text-sm text-gray-500">Warranty #{{ warranty.warranty_number }}</p>
                                </div>
                            </div>

                            <!-- Warranty Details -->
                            <div class="flex flex-wrap gap-4 items-center">
                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase">Type</p>
                                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getTypeColor(warranty.warranty_type)]">
                                        {{ warranty.warranty_type }}
                                    </span>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase">Start</p>
                                    <p class="text-sm font-medium">{{ formatDate(warranty.start_date) }}</p>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase">End</p>
                                    <p class="text-sm font-medium">{{ formatDate(warranty.end_date) }}</p>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase">Remaining</p>
                                    <p class="text-sm font-medium" :class="warranty.remaining_days <= 30 ? 'text-red-600' : warranty.remaining_days <= 90 ? 'text-yellow-600' : 'text-green-600'">
                                        {{ warranty.remaining_days }} days
                                    </p>
                                </div>

                                <div class="text-center">
                                    <p class="text-xs text-gray-500 uppercase">Status</p>
                                    <span :class="['px-2 py-1 rounded-full text-xs font-medium', getStatusColor(warranty.status_label)]">
                                        {{ warranty.status_label }}
                                    </span>
                                </div>

                                <a
                                    :href="`/profile/warranties/${warranty.id}`"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </ProfileLayout>
    </MainLayout>
</template>