<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from './Layouts/MainLayout.vue';
import CategoryIconBar from './Components/CategoryIconBar.vue';
import FlashSale from './Components/FlashSale.vue';
import ProductCarousel from './Components/ProductCarousel.vue';
import AdCarousel from './Components/AdCarousel.vue';

defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    featuredCategories: {
        type: Array,
        default: () => [],
    },
    featuredProducts: {
        type: Array,
        default: () => [],
    },
    latestProducts: {
        type: Array,
        default: () => [],
    },
    homepageAds: {
        type: Array,
        default: () => [],
    },
    saleProducts: {
        type: Array,
        default: () => [],
    },
    flashSaleEndTime: {
        type: String,
        default: '',
    },
});

// Helper function to format ad type for display
const formatAdType = (type) => {
    const typeMap = {
        'banner': 'Banner Ad',
        'promotion': 'Promotion',
        'featured': 'Featured',
        'flash_sale': 'Flash Sale',
        'category': 'Category Link'
    };
    return typeMap[type] || type;
};

// Helper function to get badge classes based on ad type
const getAdTypeBadgeClass = (type) => {
    const classMap = {
        'banner': 'bg-blue-600 text-white',
        'promotion': 'bg-green-600 text-white',
        'featured': 'bg-purple-600 text-white',
        'flash_sale': 'bg-red-600 text-white',
        'category': 'bg-orange-500 text-white'
    };
    return classMap[type] || 'bg-gray-600 text-white';
};
</script>

<template>
    <Head title="Buynow | Africa's Online Shopping" />
    <MainLayout>
        <!-- Category Icon Bar - Right below navigation -->
        <CategoryIconBar :categories="categories" />

        <!-- Homepage Ads Carousel Section -->
        <AdCarousel 
            v-if="homepageAds.length > 0" 
            :ads="homepageAds" 
            :autoplay-interval="5000"
        />

        <!-- Flash Sales Section (if there are sale products) -->
        <FlashSale 
            v-if="saleProducts.length > 0" 
            :products="saleProducts" 
            :end-time="flashSaleEndTime" 
        />

        <!-- Featured Products Carousel -->
        <ProductCarousel 
            v-if="featuredProducts.length > 0"
            title="Featured Products" 
            :products="featuredProducts" 
            link="/products?filter=featured"
        />

        <!-- Latest Products Carousel -->
        <ProductCarousel 
            v-if="latestProducts.length > 0"
            title="New Arrivals" 
            :products="latestProducts" 
            link="/products?sort=newest"
        />

        <!-- Promo Banner -->
        <section class="py-6 bg-gray-50 dark:bg-zinc-900">
            <div class="container mx-auto px-4">
                <a href="/products" class="block">
                    <div class="bg-gradient-to-r from-yellow-400 to-yellow-300 rounded-xl p-8 text-center">
                        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-2">
                            Super Savings Every Day
                        </h2>
                        <p class="text-gray-800 text-lg">
                            Shop now and enjoy up to 50% off on selected items
                        </p>
                        <span class="inline-block mt-4 bg-black text-yellow-400 font-semibold px-6 py-2 rounded-full">
                            Shop Now
                        </span>
                    </div>
                </a>
            </div>
        </section>

        <!-- Categories Grid -->
        <section v-if="featuredCategories.length > 0" class="py-8 bg-white dark:bg-zinc-900">
            <div class="container mx-auto px-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Shop by Category</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <a 
                        v-for="category in featuredCategories" 
                        :key="category.id" 
                        :href="`/products?category=${category.slug}`"
                        class="group text-center p-4 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-800 transition-colors"
                    >
                        <div class="w-16 h-16 mx-auto mb-3 bg-gray-100 dark:bg-zinc-800 rounded-full flex items-center justify-center group-hover:bg-yellow-400 transition-colors">
                            <svg class="w-8 h-8 text-gray-600 dark:text-gray-300 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                            </svg>
                        </div>
                        <h3 class="text-sm font-medium text-gray-900 dark:text-white group-hover:text-yellow-400">
                            {{ category.name }}
                        </h3>
                    </a>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="py-12 bg-gray-50 dark:bg-zinc-900">
            <div class="container mx-auto px-4 text-center">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                    Start Selling on Buynow
                </h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6 max-w-2xl mx-auto">
                    Join thousands of sellers reaching millions of customers across Africa
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a 
                        href="/stores/create" 
                        class="inline-block bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-8 py-3 rounded-lg transition-colors"
                    >
                        Create Your Store
                    </a>
                    <a 
                        href="/products" 
                        class="inline-block bg-white dark:bg-zinc-800 border border-gray-300 dark:border-zinc-700 text-gray-700 dark:text-gray-200 font-semibold px-8 py-3 rounded-lg hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors"
                    >
                        Browse Products
                    </a>
                </div>
            </div>
        </section>
    </MainLayout>
</template>
