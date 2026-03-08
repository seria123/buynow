<script setup>
import { computed, ref, reactive, onMounted, onUnmounted } from 'vue';
import { Head, router, usePage,Link} from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import { useCart } from '../../cart.js';
import { toast } from 'vue3-toastify';

import 'vue3-toastify/dist/index.css';
import ProductFilters from '../Components/ProductFilters.vue';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    filterCategories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
    attributes: {
        type: Array,
        default: () => [],
    },
    priceRange: {
        type: Object,
        default: () => ({ min: 0, max: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
    pagination: {
        type: Object,
        default: () => ({ current_page: 1, last_page: 1, per_page: 12 }),
    },
});


const wishlist = ref(props.wishlistItems || []);
const wishlisted = reactive({});

// Initialize based on wishlistItems if provided
if (props.wishlistItems?.length) {
    props.wishlistItems.forEach(p => {
        if (p?.id) wishlisted[p.id] = true;
    });
}


function addToWishlist(product) {
    if (!product?.id) return;

    // Optimistic UI toggle
    wishlisted[product.id] = !wishlisted[product.id];

    // Send request to backend
    router.post(`/profile/wishlist/${product.id}`, {}, {
        onSuccess: () => {
            toast.success(
                wishlisted[product.id] ? `${product.name} added to wishlist!` : `${product.name} removed from wishlist!`
            );
        },
        onError: () => {
            // revert toggle on error
            wishlisted[product.id] = !wishlisted[product.id];
            toast.error(`Failed to update wishlist for ${product.name}.`);
        }
    });
}
const { addToCart, loadCart } = useCart();


const handleAddToCart = async (productId) => {
    try {
        await addToCart(productId, 1);
        toast.success('Product added to cart!', { position: 'bottom-left', autoClose: 2000 });
    } catch (err) {
        toast.error('Failed to add product', { position: 'bottom-left', autoClose: 3000 });
    }
};
const normalizedFilters = computed(() => props.filters ?? {});
const page = usePage();
const sharedCategories = computed(() => page.props.categories ?? []);
const categoryTree = computed(() => (sharedCategories.value?.length ? sharedCategories.value : props.filterCategories));

const requestOptions = {
    preserveScroll: true,
    preserveState: true,
    replace: true,
};

const handleFilterApply = (newFilters) => {
    const query = buildQuery(newFilters);
    router.get('/products', query, requestOptions);
};

const clearFilters = () => {
    router.get('/products', {}, requestOptions);
};

const buildQuery = (filters) => {
    const query = {};

    // Handle multiple categories - use categories[] array parameter
    if (filters.categories?.length) {
        filters.categories.forEach((categorySlug) => {
            if (!query['categories[]']) {
                query['categories[]'] = [];
            }

            query['categories[]'].push(categorySlug);
        });
    } else if (filters.category) {
        // Backward compatibility: support single category parameter
        query.category = filters.category;
    }

    if (filters.brands?.length) {
        query.brands = filters.brands;
    }

    if (filters.price) {
        const min = Number(filters.price.min);
        const max = Number(filters.price.max);

        if (!Number.isNaN(min) && min !== props.priceRange.min) {
            query['price[min]'] = min;
        }

        if (!Number.isNaN(max) && max !== props.priceRange.max) {
            query['price[max]'] = max;
        }
    }

    Object.entries(filters.attributes ?? {}).forEach(([attributeId, values]) => {
        if (values?.length) {
            query[`attributes[${attributeId}]`] = values;
        }
    });

    return query;
};

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return null;
    }

    const numeric = Number(value);

    if (Number.isNaN(numeric)) {
        return value;
    }

    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        maximumFractionDigits: 0,
    }).format(numeric);
};

// Infinite scrolling
const isLoading = ref(false);
const currentPage = ref(props.pagination?.current_page || 1);
const hasMorePages = computed(() => currentPage.value < (props.pagination?.last_page || 1));

const loadMore = () => {
    if (isLoading.value || !hasMorePages.value) return;

    isLoading.value = true;
    currentPage.value++;

    const query = buildQuery(normalizedFilters.value);
    query.page = currentPage.value;

    router.get('/products', query, {
        preserveScroll: true,
        preserveState: true,
        replace: false,
        onSuccess: () => {
            isLoading.value = false;
        },
        onError: () => {
            isLoading.value = false;
            currentPage.value--;
        },
    });
};

const handleScroll = () => {
    const scrollPosition = window.innerHeight + window.scrollY;
    const threshold = document.documentElement.scrollHeight - 500;

    if (scrollPosition >= threshold && !isLoading.value && hasMorePages.value) {
        loadMore();
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
const saveProductImage = () => {
  productForm.post(`/products/${product.id}/update`, {
    forceFormData: true,
    onSuccess: () => {
      triggerToast('Product updated!');
      // refresh orders so the new product image shows
      router.reload({
        only: ['orders'],
        preserveState: true,
      });
    }
  });
};
function goToWishlist(product) {
  // Optional: Add product before redirect
  router.post(`/profile/wishlist/${product.id}`, {}, {
    onSuccess: () => {
      toast.success(`${product.name} added to wishlist!`);
      router.get('/profile/wishlist'); // redirect to wishlist page
    },
    onError: () => {
      toast.error('Failed to add to wishlist.');
    }
  });
}
</script>

<template>

    <Head title="Products | Buynow" />
    <MainLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-10 transition-colors duration-300">
            <div class="container mx-auto px-4 space-y-8">
                <div class="text-center space-y-3">
                    <p
                        class="text-sm font-semibold tracking-wide text-yellow-600 dark:text-yellow-400 uppercase flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z"
                                clip-rule="evenodd" />
                        </svg>
                        Shop our collection
                    </p>
                    <h1 class="text-4xl md:text-5xl font-bold text-gray-900 dark:text-white">
                        Premium Products
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto text-lg">
                        Discover our curated collection of electronics and gadgets. Filter by category, brand, or price
                        to find exactly what you need.
                    </p>
                </div>

                <div class="flex flex-col lg:flex-row gap-8">
                    <aside class="lg:w-80 shrink-0">
                        <ProductFilters :categories="categoryTree" :brands="brands" :attributes="attributes"
                            :price-range="priceRange" :filters="normalizedFilters" @apply="handleFilterApply"
                            @clear="clearFilters" />
                    </aside>

                    <div class="flex-1 space-y-6">
                        <div v-if="products.length" class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6">
                            <div v-for="product in products" :key="product.id"
                                class="group bg-white dark:bg-zinc-900 shadow-md hover:shadow-2xl dark:shadow-zinc-950/50 rounded-2xl border border-gray-100 dark:border-zinc-800 overflow-hidden flex flex-col transition-all duration-300 hover:-translate-y-1">
                                
                                <!-- Product Image -->
<div class="relative overflow-hidden bg-gray-50 dark:bg-zinc-800 rounded-t-2xl">
    <div
        class="w-full aspect-square flex items-center justify-center overflow-hidden group-hover:scale-105 transition-transform duration-500"
    >
        <!-- Safe product thumbnail -->
      <img
    :src="product.thumbnail_url || '/images/placeholder.png'"
    :alt="product.name"
    class="w-full h-full object-cover"
    
/>
    </div>
                                    <!-- Stock Badge -->
                                    <div class="absolute top-3 right-3">
                                        <span v-if="product.stats.total_stock > 0"
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-green-500 text-white shadow-lg">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            In Stock
                                        </span>
                                        <span v-else
                                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-500 text-white shadow-lg">
                                            Out of Stock
                                        </span>
                                    </div>
                                </div>

                                <!-- Product Info -->
                                <div class="p-5 flex-1 flex flex-col">
                                    <!-- Category & Brand -->
                                    <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400 mb-2">
                                        <span
                                            class="px-2 py-0.5 rounded-full bg-gray-100 dark:bg-zinc-800 font-medium">{{
                                                product.category?.name ?? 'Uncategorized' }}</span>
                                        <span>•</span>
                                        <span class="font-medium">{{ product.brand?.name ?? 'No brand' }}</span>
                                    </div>

                                    <!-- Product Name -->
                                    <Link
  :href="route('products.show', product.id)"
  class="text-lg font-bold text-gray-900 dark:text-white line-clamp-2 mb-3 group-hover:text-yellow-600 dark:group-hover:text-yellow-400 transition-colors"
>
  {{ product.name }}
</Link>

                                    <!-- Price -->
                                    <div class="mt-auto">
                                        <div class="flex items-baseline gap-2 mb-3">
                                            <div class="text-2xl font-bold text-gray-900 dark:text-white">
                                                {{ formatCurrency(product.price) ?? '—' }}
                                            </div>
                                             <svg
    @click="product?.id && addToWishlist(product)"
    xmlns="http://www.w3.org/2000/svg"
    :fill="product?.id ? (wishlisted[product.id] ?? false) ? 'red' : 'black' : 'black'"
    viewBox="0 0 24 24"
    class="w-6 h-6 cursor-pointer hover:scale-110 transition-transform duration-150"
    title="Add to Wishlist"
>
    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
             2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
             C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
             c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
</svg>
                                        </div>


                                        <!-- Stats -->
                                        <div class="flex items-center gap-3 text-xs text-gray-500 dark:text-gray-400 mb-4">
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                </svg>
                                                <span class="font-semibold">{{ product.stats.variant_count }}</span>
                                                <span>variants</span>
                                            </div>
                                            <span>•</span>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                                </svg>
                                                <span class="font-semibold">{{ product.stats.total_stock }}</span>
                                                <span>units</span>
                                            </div>
                                        </div>

                                        <!-- Add to Cart Button -->
                                       <button @click="handleAddToCart(product.id)"
                                            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 text-white font-bold py-3 shadow-lg shadow-yellow-500/30 hover:shadow-yellow-600/40 transition-all hover:scale-[1.02] active:scale-[0.98]">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                            </svg>
                                            Add to Cart
                                        </button>

                                        
  
                                    </div>
                                </div>

                                <!-- Variant Badges (if any) -->
                                <div v-if="product.variant_badges?.length"
                                    class="px-5 pb-4 border-t border-gray-100 dark:border-zinc-800 pt-3">
                                    <div class="flex flex-wrap gap-2">
                                        <span v-for="badge in product.variant_badges.slice(0, 3)"
                                            :key="badge.attribute + badge.value"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300">
                                            {{ badge.attribute }}: <span class="ml-1 text-yellow-600 dark:text-yellow-400">{{
                                                badge.value }}</span>
                                        </span>
                                        <span v-if="product.variant_badges.length > 3"
                                            class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400">
                                            +{{ product.variant_badges.length - 3 }} more
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Loading Indicator -->
                        <div v-if="isLoading" class="flex justify-center py-8">
                            <div class="flex items-center gap-3 text-gray-600 dark:text-gray-400">
                                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                        stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                    </path>
                                </svg>
                                <span class="font-medium">Loading more products...</span>
                            </div>
                        </div>

                        <!-- No Products Found -->
                        <div v-if="!products.length && !isLoading"
                            class="rounded-2xl border-2 border-dashed border-gray-200 dark:border-zinc-800 bg-white dark:bg-zinc-900 p-16 text-center">
                            <div
                                class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-yellow-50 dark:bg-yellow-900/20 text-yellow-500 dark:text-yellow-400 mb-4">
                                <svg class="w-10 h-10" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">No products found</h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-6">
                                Try adjusting your filters to discover more items.
                            </p>
                            <button @click="clearFilters"
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-yellow-500 hover:bg-yellow-600 text-white font-semibold transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                Clear Filters
                            </button>
                        </div>

                        <!-- End of Results -->
                        <div v-if="products.length && !hasMorePages && !isLoading"
                            class="text-center py-8 text-gray-500 dark:text-gray-400">
                            <p class="font-medium">You've reached the end of the product list</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MainLayout>
</template>
