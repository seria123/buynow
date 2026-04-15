<script setup>
defineProps({
    products: {
        type: Array,
        default: () => [],
    },
});

const getProductImage = (product) => {
    return product.thumbnail_url || product.images?.[0]?.url || '/images/placeholder.svg';
};

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
    }).format(price || 0);
};
</script>

<template>
    <!-- Recently Viewed Section -->
    <section v-if="products && products.length > 0" class="bg-white dark:bg-zinc-900 py-6 border-t border-gray-100 dark:border-zinc-800">
        <div class="container mx-auto px-4">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <!-- Clock Icon -->
                    <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">Recently Viewed</h2>
                </div>
            </div>

            <!-- Products Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-8 gap-4">
                <a
                    v-for="product in products"
                    :key="product.id"
                    :href="`/products/${product.slug}`"
                    class="group block"
                >
                    <div class="bg-gray-50 dark:bg-zinc-800 rounded-lg overflow-hidden transition-shadow hover:shadow-md">
                        <!-- Product Image -->
                        <div class="aspect-square relative overflow-hidden bg-gray-100 dark:bg-zinc-700">
                            <img
                                :src="getProductImage(product)"
                                :alt="product.name"
                                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
                                loading="lazy"
                            />
                        </div>
                        <!-- Product Info -->
                        <div class="p-2">
                            <h3 class="text-sm text-gray-700 dark:text-gray-300 line-clamp-2 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
                                {{ product.name }}
                            </h3>
                            <p class="mt-1 font-bold text-gray-900 dark:text-white text-sm">
                                {{ formatPrice(product.price) }}
                            </p>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>
</template>