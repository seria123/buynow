<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    endTime: {
        type: String,
        required: true,
    },
});

const timeLeft = ref({
    hours: 0,
    minutes: 0,
    seconds: 0,
});

let intervalId = null;

const calculateTimeLeft = () => {
    const end = new Date(props.endTime).getTime();
    const now = new Date().getTime();
    const diff = end - now;

    if (diff > 0) {
        timeLeft.value = {
            hours: Math.floor((diff / (1000 * 60 * 60)) % 24),
            minutes: Math.floor((diff / 1000 / 60) % 60),
            seconds: Math.floor((diff / 1000) % 60),
        };
    } else {
        timeLeft.value = { hours: 0, minutes: 0, seconds: 0 };
    }
};

onMounted(() => {
    calculateTimeLeft();
    intervalId = setInterval(calculateTimeLeft, 1000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});

const formatNumber = (num) => String(num).padStart(2, '0');

const getDiscountPercentage = (product) => {
    if (product.price && product.compare_price) {
        return Math.round(((product.price - product.compare_price) / product.price) * 100);
    }
    return 0;
};

const getProductImage = (product) => {
    return product.thumbnail_url || '/images/placeholder.png';
}; // <-- Added missing closing brace
</script>

<template>
    
    <!-- Flash Sale Section -->
    <section class="bg-white dark:bg-zinc-900 py-6">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <!-- Flash Icon -->
                    <div class="w-10 h-10 bg-yellow-400 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Flash Sales</h2>
                    <span class="text-sm text-gray-500 dark:text-gray-400">Ends in:</span>
                </div>
                
                <!-- Countdown Timer -->
                <div class="flex items-center gap-1">
                    <div class="bg-yellow-400 text-black px-2 py-1 rounded font-bold text-sm">
                        {{ formatNumber(timeLeft.hours) }}
                    </div>
                    <span class="text-yellow-400 font-bold">:</span>
                    <div class="bg-yellow-400 text-black px-2 py-1 rounded font-bold text-sm">
                        {{ formatNumber(timeLeft.minutes) }}
                    </div>
                    <span class="text-yellow-400 font-bold">:</span>
                    <div class="bg-yellow-400 text-black px-2 py-1 rounded font-bold text-sm">
                        {{ formatNumber(timeLeft.seconds) }}
                    </div>
                </div>

                <a href="/products?filter=sale" class="text-yellow-400 font-semibold text-sm hover:underline">
                    See All
                </a>
            </div>

            <!-- Products Carousel -->
            <div class="relative">
                <div class="flex overflow-x-auto gap-4 pb-4 scrollbar-hide scroll-smooth" style="scroll-behavior: smooth;">
                    <a 
                        v-for="product in products" 
                        :key="product.id" 
                        :href="`/products/${product.slug}`"
                        class="flex-shrink-0 w-[160px] group"
                    >
                        <div class="bg-white dark:bg-zinc-800 rounded-lg border border-gray-200 dark:border-zinc-700 overflow-hidden hover:shadow-lg transition-shadow">
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
                            </div>
                            
                            <!-- Product Info -->
                            <div class="p-3">
                                <h3 class="text-sm font-medium text-gray-900 dark:text-white line-clamp-2 mb-2 group-hover:text-yellow-400">
                                    {{ product.name }}
                                </h3>
                                <div class="space-y-1">
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
