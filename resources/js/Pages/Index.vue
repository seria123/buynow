<script setup>
import { Head } from '@inertiajs/vue3';
import MainLayout from './Layouts/MainLayout.vue';
import FeaturedCategories from './Components/FeaturedCategories.vue';

defineProps({
    featuredCategories: {
        type: Array,
        default: () => [],
    },
});
</script>

<template>
    <Head title="Buynow | Home" />
    <MainLayout>
        <section class="bg-gradient-to-br from-yellow-50 via-lime-100 to-teal-50 py-40 min-h-[650px] flex items-center">
            <div class="container mx-auto px-4 flex flex-col md:flex-row items-center justify-between gap-8">
                <div class="max-w-xl">
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 mb-6">
                        Welcome to <span class="text-lime-600">Buynow</span>
                    </h1>
                    <p class="text-lg text-gray-600 mb-8">
                        Discover an easy way to manage and review your store's products.
                    </p>
                    <div class="flex gap-4">
                        <a
                            href="#"
                            class="inline-block bg-lime-600 text-white font-semibold px-6 py-3 rounded-md shadow hover:bg-lime-700 transition"
                        >
                            Explore Products
                        </a>
                        <a
                            href="#"
                            class="inline-block bg-white border border-lime-600 text-lime-700 font-semibold px-6 py-3 rounded-md hover:bg-lime-50 transition"
                        >
                            Learn More
                        </a>
                    </div>
                </div>
                <div class="flex-1 flex justify-center">
                    <!-- Temporary illustration/hero image (replace src with actual image if needed) -->
                    <img
                        src="https://illustrations.popsy.co/gray/market.svg"
                        alt="Shopping illustration"
                        class="max-w-xs md:max-w-md rounded-xl shadow"
                    />
                </div>
            </div>
        </section>

        <FeaturedCategories :categories="featuredCategories" />
    </MainLayout>
</template>
