<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import { useCart } from '../../cart.js';
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';

const props = defineProps({
  product: Object
});
const wishlist = ref([]); // local wishlist
const quantity = ref(1);

const { addToCart } = useCart();


// Format price in KES
const formatCurrency = (value) => {
  if (value === null || value === undefined) return '—';
  return new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
  }).format(Number(value));
};
function addToWishlist(productId) {
  router.post(`/profile/wishlist/${productId}`, {}, {
    onSuccess: () => {
      router.get('/profile/wishlist');
    }
  });
}

// Add to cart handler
const handleAddToCart = async () => {
  if (!props.product?.id) return;
  try {
    await addToCart(props.product.id, quantity.value);
    toast.success('Product added to cart!', { position: 'bottom-left', autoClose: 2000 });
  } catch (err) {
    toast.error('Failed to add product', { position: 'bottom-left', autoClose: 3000 });
  }
};

// Go back to product listing
const goBack = () => router.get('/products', {}, { preserveScroll: true });
</script>

<template>
  <MainLayout>
    <Head :title="product?.name ?? 'Product Details'" />

    <div class="container mx-auto py-10 px-4">
      <button @click="goBack" class="mb-6 px-4 py-2 bg-gray-200 dark:bg-zinc-700 rounded hover:bg-gray-300">
        ← Back to Products
      </button>

      <div class="flex flex-col lg:flex-row gap-8">
        <!-- Product Image -->
        <div class="lg:w-1/2">
          <img
            v-if="product?.thumbnail_url"
            :src="product.thumbnail_url"
            :alt="product.name"
            class="w-full h-auto object-cover rounded-lg shadow-md"
          />
          <div v-else class="w-full h-96 bg-gray-200 dark:bg-zinc-800 flex items-center justify-center rounded-lg">
            No image available
          </div>
        </div>

        <!-- Product Info -->
        <div class="lg:w-1/2 flex flex-col gap-4">
          <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ product?.name }}</h1>

          <div class="text-gray-600 dark:text-gray-300">
            Category: {{ product?.category?.name ?? 'Uncategorized' }} <br>
            Brand: {{ product?.brand?.name ?? 'No Brand' }}
          </div>

          <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(product?.price) }}</div>

          <!-- Stock -->
          <div v-if="product?.stats?.total_stock > 0" class="text-green-600 font-semibold">
            In Stock: {{ product.stats.total_stock }} units
          </div>
          <div v-else class="text-red-600 font-semibold">
            Out of Stock
          </div>

          <!-- Variants -->
          <div v-if="product?.variant_badges?.length" class="flex flex-wrap gap-2">
            <span
              v-for="badge in product.variant_badges"
              :key="badge.attribute + badge.value"
              class="px-2 py-1 bg-gray-100 dark:bg-zinc-800 rounded text-sm font-medium text-gray-700 dark:text-gray-300"
            >
              {{ badge.attribute }}: {{ badge.value }}
            </span>
          </div>

          <!-- Add to Cart -->
          <div class="mt-6 flex items-center gap-4">
            <input
              type="number"
              v-model.number="quantity"
              min="1"
              :max="product?.stats?.total_stock || 1"
              class="w-20 p-2 border rounded"
            />
            <button
              @click="handleAddToCart"
              :disabled="product?.stats?.total_stock === 0"
              class="px-6 py-3 bg-yellow-600 text-white rounded hover:bg-yellow-700 disabled:opacity-50"
            >
              Add to Cart
            </button>
            <svg
  @click="addToWishlist(product.id)"
  xmlns="http://www.w3.org/2000/svg"
  fill="black"
  viewBox="0 0 24 24"
  class="w-6 h-6 cursor-pointer hover:opacity-80 transition"
  title="Add to Wishlist"
>
  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
           2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09
           C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5
           c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
</svg>
          </div>

          <!-- Description -->
          <div class="mt-6 text-gray-700 dark:text-gray-300">
            <h2 class="font-semibold mb-2">Description</h2>
            <p>{{ product?.description ?? 'No description available.' }}</p>
          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>