<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';

const searchQuery = ref('');
const isSearching = ref(false);

const searchForm = useForm({
    q: '',
});

const handleSearch = () => {
    if (searchQuery.value.trim()) {
        isSearching.value = true;
        searchForm.q = searchQuery.value;
        searchForm.get('/products', {
            onFinish: () => {
                isSearching.value = false;
            },
        });
    }
};
</script>

<template>
    <!-- Search Bar Section -->
    <section class="bg-orange-500 py-3">
        <div class="container mx-auto px-4">
            <form @submit.prevent="handleSearch" class="max-w-3xl mx-auto">
                <div class="flex items-center bg-white rounded-lg overflow-hidden shadow-md">
                    <!-- Search Input -->
                    <div class="flex-1 flex items-center px-4">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input 
                            v-model="searchQuery"
                            type="text" 
                            placeholder="Search for products, brands and categories..."
                            class="w-full px-3 py-3 text-gray-700 dark:text-gray-200 focus:outline-none"
                        >
                    </div>
                    
                    <!-- Search Button -->
                    <button 
                        type="submit"
                        :disabled="isSearching || !searchQuery.trim()"
                        class="bg-orange-500 hover:bg-orange-600 text-white font-semibold px-6 py-3 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="!isSearching">Search</span>
                        <svg v-else class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </button>
                </div>
            </form>
        </div>
    </section>
</template>
