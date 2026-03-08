<script setup>
import { ref } from 'vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Products',
    },
    products: {
        type: Array,
        default: () => [],
    },
    link: {
        type: String,
        default: '/products',
    },
});

const scrollContainer = ref(null);

const scrollLeft = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: -300, behavior: 'smooth' });
    }
};

const scrollRight = () => {
    if (scrollContainer.value) {
        scrollContainer.value.scrollBy({ left: 300, behavior: 'smooth' });
    }
};

const getProductImage = (product) => {
    return product.thumbnail_url || '/images/placeholder.png';
};

const getDiscountPercentage = (product) => {
    if (product.price && product.compare_price) {
        return Math.round(((product.price - product.compare_price) / product.price) * 100);
    }
    return 0;
};
</script>

<template>
    <section class="py-6 bg-gray-50 dark:bg-zinc-900">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">{{ title }}</h2>
                <a :href="link" class="text-yellow-400 font-semibold text-sm hover:underline">
                    See All
                </a>
            </div>

            <!-- Carousel with Navigation -->
            <div class="relative group">
                <!-- Left Navigation Button -->
                <button 
                    @click="scrollLeft"
                    class="absolute left-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white dark:bg-zinc-800 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-zinc-700"
                >
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                </button>

                <!-- Products Container -->
                <div 
                    ref="scrollContainer"
                    class="flex overflow-x-auto gap-4 pb-4 scrollbar-hide scroll-smooth"
                    style="scroll-behavior: smooth;"
                >
                    <a 
                        v-for="product in products" 
                        :key="product.id" 
                        :href="`/products/${product.slug}`"
                        class="flex-shrink-0 w-[180px] group"
                    >
                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 overflow-hidden hover:shadow-lg transition-shadow h-full">
                            <!-- Product Image -->
                            <div class="relative aspect-square bg-gray-100 dark:bg-zinc-700">
                                <img 
                                    :src="getProductImage(product)" 
                                    :alt="product.name"
                                    class="w-full h-full object-cover"
                                >
                                <!-- Discount Badge -->
                                <div v-if="getDiscountPercentage(product) > 0" class="absolute top-2 left-2 bg-yellow-400 text-black text-xs font-bold px-2 py-0.5 rounded">
                                    -{{ getDiscountPercentage(product) }}%
                                </div>
                                <!-- Featured Badge -->
                                <div v-if="product.is_featured" class="absolute top-2 right-2 bg-lime-500 text-white text-xs font-bold px-2 py-0.5 rounded">
                                    Featured
                                </div>
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-3 flex flex-col flex-grow">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 mb-2 group-hover:text-yellow-400 transition-colors">
                                    {{ product.name }}
                                </h3>
                                
                                <!-- Category -->
                                <p v-if="product.category" class="text-xs text-gray-500 dark:text-gray-400 mb-2">
                                    {{ product.category.name }}
                                </p>
                                
                                <div class="mt-auto space-y-1">
                                    <div v-if="product.compare_price" class="flex items-center gap-2">
                                        <span class="text-yellow-400 font-bold">
                                            KSh {{ Number(product.compare_price).toLocaleString() }}
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <span :class="product.compare_price ? 'text-gray-400 text-sm line-through' : 'text-yellow-400 font-bold'">
                                            KSh {{ Number(product.price).toLocaleString() }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                </div>

                <!-- Right Navigation Button -->
                <button 
                    @click="scrollRight"
                    class="absolute right-0 top-1/2 -translate-y-1/2 z-10 w-10 h-10 bg-white dark:bg-zinc-800 rounded-full shadow-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity hover:bg-gray-100 dark:hover:bg-zinc-700"
                >
                    <svg class="w-5 h-5 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>
        </div>
    </section>
</template>

<style scoped>
.scrollbar-hide::-webkit-scrollbar {
    display: none;
}
.scrollbar-hide {
    -ms-overflow-style: none;
    scrollbar-width: none;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
