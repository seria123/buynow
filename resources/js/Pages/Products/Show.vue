<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import { useCart } from '../../cart.js';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
  product: Object
});

const isWishlistLoading = ref(false);
const quantity = ref(1);

const { addToCart, checkAuthStatus, isLoggedIn } = useCart();

// Check auth status on mount
onMounted(async () => {
  await checkAuthStatus();
});

// Format price in KES
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '—';
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
  }).format(Number(value));
};

// Calculate discount percentage
const discountPercentage = computed(() => {
  if (props.product?.price && props.product?.compare_price) {
    return Math.round(((props.product.price - props.product.compare_price) / props.product.price) * 100);
  }
  return 0;
});

// Add to wishlist handler - just redirect to wishlist page
const addToWishlist = () => {
  if (!isLoggedIn.value) {
    router.get('/login', {}, { 
      preserveScroll: true,
      data: { redirect: window.location.href }
    });
    return;
  }
  // Redirect to wishlist page
  router.visit('/profile/wishlist');
};

// Add to cart handler
const handleAddToCart = async () => {
  // Debug: log the entire product object
  console.log('Product props:', JSON.stringify(props.product));
  
  // Try different ways to get product ID or slug
  let productId = props.product?.id;
  let productSlug = props.product?.slug;
  
  // If no ID, try slug from props
  if (!productId && productSlug) {
    // Use slug - will be sent as product_slug to backend
  }
  
  // If still no ID or slug, try to get from URL
  if (!productId && !productSlug) {
    const pathParts = window.location.pathname.split('/');
    productSlug = pathParts[pathParts.length - 1];
  }
  
  if (!productId && !productSlug) {
    toast.error('Cannot find product', { position: 'bottom-left', autoClose: 3000 });
    return;
  }
  
  try {
    // Send product_id if available, otherwise send product_slug
    if (productId) {
      await addToCart(productId, quantity.value);
    } else {
      // Use slug - create a custom add to cart with slug
      const csrf = document.querySelector('meta[name="csrf-token"]')?.content || 
                   document.cookie.split('; ').find(c => c.startsWith('XSRF-TOKEN='))?.split('=')[1];
      await fetch('/cart/add', {
        method: 'POST',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': csrf || '',
          'X-Requested-With': 'XMLHttpRequest',
          'Accept': 'application/json',
        },
        body: JSON.stringify({ product_slug: productSlug, quantity: quantity.value }),
      });
    }
    toast.success('Product added to cart!', { position: 'bottom-left', autoClose: 2000 });
  } catch (err) {
    toast.error('Failed to add product to cart', { position: 'bottom-left', autoClose: 3000 });
  }
};

// Go back to product listing
const goBack = () => router.get('/products', {}, { preserveScroll: true });
</script>

<template>
  <MainLayout>
    <Head :title="product?.name ?? 'Product Details'" />

    <div class="container mx-auto py-6 px-2 md:px-4">
      <!-- Back Button -->
      <button @click="goBack" class="mb-4 text-sm text-gray-600 hover:text-yellow-500 flex items-center gap-1 transition-colors">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
        </svg>
        Back to Products
      </button>

      <div class="flex flex-col lg:flex-row gap-6 lg:gap-10">
        <!-- Product Image -->
        <div class="lg:w-1/2">
          <div class="relative bg-white rounded-lg border border-gray-200 overflow-hidden">
            <!-- Discount Badge -->
            <div v-if="discountPercentage > 0" class="absolute top-0 left-0 z-10 bg-yellow-400 text-black text-sm font-bold px-3 py-1 rounded-br-lg">
              -{{ discountPercentage }}%
            </div>
            
            <img
              v-if="product?.thumbnail_url"
              :src="product.thumbnail_url"
              :alt="product.name"
              class="w-full h-[300px] md:h-[400px] lg:h-[500px] object-contain bg-white"
            />
            <div v-else class="w-full h-[300px] md:h-[400px] lg:h-[500px] bg-gray-100 flex items-center justify-center">
              <span class="text-gray-400">No image available</span>
            </div>
          </div>

          <!-- Product Gallery -->
          <div v-if="product?.images && product.images.length > 0" class="mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Product Images</h3>
            <div class="flex gap-2 overflow-x-auto">
              <div 
                v-for="(img, index) in product.images" 
                :key="index"
                class="w-20 h-20 flex-shrink-0 rounded-lg border border-gray-200 overflow-hidden cursor-pointer hover:border-yellow-400 transition-colors"
              >
                <img :src="img.thumb_url || img.url" :alt="product.name" class="w-full h-full object-cover" />
              </div>
            </div>
          </div>
        </div>

        <!-- Product Info -->
        <div class="lg:w-1/2 flex flex-col gap-4">
          <h1 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ product?.name }}</h1>

          <div class="text-sm text-gray-500 dark:text-gray-400">
            <span>Category: </span>
            <a :href="`/products?category=${product?.category?.slug}`" class="text-yellow-600 hover:underline">
              {{ product?.category?.name ?? 'Uncategorized' }}
            </a>
            <span class="mx-2">|</span>
            <span>Brand: </span>
            <a :href="`/products?brands[]=${product?.brand?.id}`" class="text-yellow-600 hover:underline">
              {{ product?.brand?.name ?? 'No Brand' }}
            </a>
          </div>

          <!-- Price Section -->
          <div class="bg-gray-50 dark:bg-zinc-800 p-4 rounded-lg">
            <div class="flex items-baseline gap-3">
              <span class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white">
                {{ formatCurrency(product?.price) }}
              </span>
              <span v-if="product?.compare_price" class="text-lg text-gray-400 line-through">
                {{ formatCurrency(product?.compare_price) }}
              </span>
            </div>
            <div v-if="discountPercentage > 0" class="text-green-600 text-sm font-medium mt-1">
              You save {{ discountPercentage }}%
            </div>
          </div>

          <!-- Stock Status -->
          <div v-if="product?.stats?.total_stock > 0" class="flex items-center gap-2 text-green-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">In Stock ({{ product.stats.total_stock }} available)</span>
          </div>
          <div v-else class="flex items-center gap-2 text-red-600">
            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
            <span class="font-medium">Out of Stock</span>
          </div>

          <!-- Star Rating -->
          <div class="flex items-center gap-2">
            <div class="flex items-center">
              <svg v-for="i in 5" :key="i" class="w-5 h-5" :class="i <= (product?.rating || 4) ? 'text-yellow-400' : 'text-gray-300'" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </div>
            <span class="text-sm text-gray-500">({{ product?.rating_count || 0 }} reviews)</span>
          </div>

          <!-- Shipping Fee -->
          <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            <span>Shipping: </span>
            <span class="font-medium" :class="product?.shipping_fee == 0 ? 'text-green-600' : ''">
              {{ product?.shipping_fee == 0 ? 'Free' : formatCurrency(product?.shipping_fee) }}
            </span>
          </div>

          <!-- Variants -->
          <div v-if="product?.variants?.length > 0" class="py-3 border-t border-b border-gray-200 dark:border-zinc-700">
            <h3 class="font-medium text-gray-700 dark:text-gray-300 mb-2">Select Variant:</h3>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="variant in product.variants"
                :key="variant.id"
                class="px-3 py-1.5 border border-gray-300 dark:border-zinc-600 rounded text-sm hover:border-yellow-400 hover:text-yellow-600 transition-colors"
              >
                {{ variant.variantOptions?.map(v => v.value).join(' / ') || variant.name }}
              </button>
            </div>
          </div>

          <!-- Quantity and Actions -->
          <div class="mt-2 space-y-4">
            <!-- Quantity Selector -->
            <div class="flex items-center gap-4">
              <span class="text-gray-700 dark:text-gray-300 font-medium">Quantity:</span>
              <div class="flex items-center border border-gray-300 dark:border-zinc-600 rounded">
                <button 
                  @click="quantity > 1 && quantity--" 
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
                  :disabled="quantity <= 1"
                >
                  -
                </button>
                <input
                  type="number"
                  v-model.number="quantity"
                  min="1"
                  :max="product?.stats?.total_stock || 1"
                  class="w-16 text-center border-x border-gray-300 dark:border-zinc-600 py-2 focus:outline-none"
                />
                <button 
                  @click="quantity < (product?.stats?.total_stock || 1) && quantity++" 
                  class="px-3 py-2 hover:bg-gray-100 dark:hover:bg-zinc-700 transition-colors"
                  :disabled="quantity >= (product?.stats?.total_stock || 1)"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Add to Cart and Buy Now Buttons -->
            <div class="flex flex-col sm:flex-row gap-3">
              <button
                @click="handleAddToCart"
                class="flex-1 py-3 px-6 bg-yellow-400 text-black font-bold rounded hover:bg-yellow-500 transition-colors flex items-center justify-center gap-2"
              >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                ADD TO CART
              </button>
            </div>

            <!-- Wishlist -->
            <button
              @click="addToWishlist"
              class="w-full py-3 px-6 border-2 border-yellow-400 text-yellow-600 font-bold rounded hover:bg-yellow-50 dark:hover:bg-zinc-800 transition-colors flex items-center justify-center gap-2"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="1.5"
                stroke="currentColor"
                class="w-5 h-5"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
              </svg>
              {{ isLoggedIn ? 'ADD TO WISHLIST' : 'LOGIN TO ADD TO WISHLIST' }}
            </button>
          </div>

          <!-- Delivery Info -->
          <div class="mt-4 p-4 bg-gray-50 dark:bg-zinc-800 rounded-lg space-y-2">
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
              </svg>
              <span>Free delivery on orders above KSh 1,000</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span>Quality assured</span>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
              <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              <span>7 day return policy</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Description -->
      <div class="mt-8">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Product Description</h2>
        <div class="bg-white dark:bg-zinc-800 border border-gray-200 dark:border-zinc-700 rounded-lg p-6">
          <p class="text-gray-700 dark:text-gray-300 leading-relaxed whitespace-pre-line">
            {{ product?.description ?? product?.short_description ?? 'No description available.' }}
          </p>
        </div>
      </div>
    </div>
  </MainLayout>
</template>