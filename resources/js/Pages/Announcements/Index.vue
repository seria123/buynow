<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

defineProps({
    announcements: {
        type: Array,
        default: () => [],
    },
});

const getTypeColor = (type) => {
    const colors = {
        general: 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300',
        promotional: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-400',
        order: 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-400',
        account: 'bg-purple-100 dark:bg-purple-900/30 text-purple-800 dark:text-purple-400',
        newsletter: 'bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-400',
    };
    return colors[type] || colors.general;
};

const getTypeLabel = (type) => {
    const labels = {
        general: 'General',
        promotional: 'Promotional',
        order: 'Order Update',
        account: 'Account',
        newsletter: 'Newsletter',
    };
    return labels[type] || 'General';
};

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head title="Announcements | Buynow" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto">
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">
                        Announcements
                    </h1>

                    <div v-if="announcements.length === 0" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-8 text-center">
                        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                        <p class="text-gray-500 dark:text-gray-400">
                            No announcements at this time.
                        </p>
                        <p class="text-sm text-gray-400 dark:text-gray-500 mt-2">
                            Check back later for updates and offers.
                        </p>
                    </div>

                    <div v-else class="space-y-4">
                        <a
                            v-for="announcement in announcements"
                            :key="announcement.id"
                            :href="`/announcements/${announcement.id}`"
                            class="block bg-white dark:bg-zinc-800 rounded-lg shadow hover:shadow-md transition-shadow p-6"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-center gap-2 mb-2">
                                        <span
                                            :class="[
                                                'inline-block px-2 py-1 text-xs font-medium rounded-full',
                                                getTypeColor(announcement.type)
                                            ]"
                                        >
                                            {{ getTypeLabel(announcement.type) }}
                                        </span>
                                        <span class="text-sm text-gray-500 dark:text-gray-400">
                                            {{ formatDate(announcement.sent_at) }}
                                        </span>
                                    </div>
                                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                                        {{ announcement.title }}
                                    </h2>
                                    <p class="text-gray-600 dark:text-gray-300 line-clamp-2">
                                        {{ announcement.content }}
                                    </p>
                                </div>
                                <svg class="w-5 h-5 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
