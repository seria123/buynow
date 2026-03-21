<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

defineProps({
    announcement: {
        type: Object,
        required: true,
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

const stripHtml = (html) => {
    const tmp = document.createElement('div');
    tmp.innerHTML = html;
    return tmp.textContent || tmp.innerText || '';
};
</script>

<template>
    <Head :title="announcement.title + ' | Buynow'" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-8">
            <div class="container mx-auto px-4">
                <div class="max-w-3xl mx-auto">
                    <a
                        href="/announcements"
                        class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white mb-6"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Announcements
                    </a>

                    <article class="bg-white dark:bg-zinc-800 rounded-lg shadow p-8">
                        <header class="mb-6">
                            <div class="flex items-center gap-3 mb-4">
                                <span
                                    :class="[
                                        'inline-block px-3 py-1 text-sm font-medium rounded-full',
                                        getTypeColor(announcement.type)
                                    ]"
                                >
                                    {{ getTypeLabel(announcement.type) }}
                                </span>
                                <span class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ formatDate(announcement.sent_at) }}
                                </span>
                            </div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ announcement.title }}
                            </h1>
                        </header>

                        <div class="prose dark:prose-invert max-w-none">
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {{ stripHtml(announcement.content) }}
                            </p>
                        </div>

                        <footer class="mt-8 pt-6 border-t border-gray-200 dark:border-zinc-700">
                            <p class="text-sm text-gray-500 dark:text-gray-400">
                                Sent to {{ announcement.recipients_count || 0 }} recipients
                            </p>
                        </footer>
                    </article>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
