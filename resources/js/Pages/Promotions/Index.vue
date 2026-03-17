<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

defineProps({
    promotions: {
        type: Array,
        default: () => [],
    },
});

const formatDiscount = (promotion) => {
    if (promotion.type === 'percentage') {
        return `${promotion.value}% OFF`;
    }
    return `KSh ${Number(promotion.value).toLocaleString()} OFF`;
};

const formatDate = (date) => {
    if (!date) return 'No expiration';
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const getValidUntil = (promotion) => {
    if (promotion.expires_at) {
        return formatDate(promotion.expires_at);
    }
    return 'No expiration';
};
</script>

<template>
    <Head title="Promotions | Buynow" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-4xl mx-auto">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Available Promotions
                    </h1>

                    <div v-if="promotions.length === 0" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">
                            No active promotions at the moment.
                        </p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                            Check back later for special offers!
                        </p>
                    </div>

                    <div v-else class="grid gap-6 md:grid-cols-2">
                        <div
                            v-for="promotion in promotions"
                            :key="promotion.id"
                            class="bg-white dark:bg-zinc-800 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-shadow border border-gray-200 dark:border-zinc-700"
                        >
                            <div class="bg-gradient-to-r from-yellow-400 to-yellow-500 p-6">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <h2 class="text-2xl font-bold text-black">
                                            {{ formatDiscount(promotion) }}
                                        </h2>
                                        <p class="text-black/80 mt-1">
                                            {{ promotion.name }}
                                        </p>
                                    </div>
                                    <div class="bg-white/20 rounded-lg px-3 py-1">
                                        <span class="text-sm font-semibold text-black">
                                            {{ promotion.type === 'percentage' ? '%' : 'KSh' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="p-6">
                                <div class="mb-4">
                                    <label class="text-xs text-gray-500 dark:text-gray-400 uppercase tracking-wide">
                                        Promo Code
                                    </label>
                                    <div class="mt-1 flex items-center gap-2">
                                        <code class="flex-1 bg-gray-100 dark:bg-zinc-700 px-4 py-2 rounded-lg font-mono text-lg font-semibold text-gray-900 dark:text-white">
                                            {{ promotion.code }}
                                        </code>
                                        <button
                                            @click="navigator.clipboard.writeText(promotion.code)"
                                            class="p-2 text-gray-500 hover:text-yellow-500 transition-colors"
                                            title="Copy code"
                                        >
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>

                                <div class="space-y-2 text-sm text-gray-600 dark:text-gray-300">
                                    <div v-if="promotion.minimum_order_amount" class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        <span>Min. order: KSh {{ Number(promotion.minimum_order_amount).toLocaleString() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        <span>Valid until: {{ getValidUntil(promotion) }}</span>
                                    </div>
                                </div>

                                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-zinc-700">
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        Use this code at checkout to redeem your discount
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
