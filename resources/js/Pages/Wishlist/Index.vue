<script setup>
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';
import ProfileLayout from '../Layouts/ProfileLayout.vue';

const props = defineProps({
  wishlistItems: {
    type: Array,
    default: () => []
  }
});

const page = usePage();
const flash = computed(() => page.props.flash || {});

const oldItems = computed(() => props.wishlistItems.filter(p => p.is_old));

function removeItem(product) {
  router.delete(`/profile/wishlist/${product.id}`);
}

function deleteOldItems() {
  if (!confirm(`Delete ${oldItems.value.length} item(s) older than 30 days?`)) return;
  router.delete('/profile/wishlist-old', {
    preserveScroll: true,
  });
}

function formatDate(iso) {
  return new Date(iso).toLocaleDateString('en-KE', { year: 'numeric', month: 'short', day: 'numeric' });
}
</script>

<template>
  <MainLayout>
    <ProfileLayout>
      <div class="py-4">
        <div class="flex items-center justify-between mb-6">
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">My Wishlist</h1>
          <button
            v-if="oldItems.length > 0"
            @click="deleteOldItems"
            class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white text-sm font-semibold px-4 py-2 rounded-xl transition"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
            </svg>
            Delete old items ({{ oldItems.length }})
          </button>
        </div>

        <!-- Flash message -->
        <div v-if="flash.success" class="mb-4 bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
          {{ flash.success }}
        </div>

        <!-- Old items notice -->
        <div v-if="oldItems.length > 0" class="mb-4 bg-amber-50 border border-amber-200 text-amber-800 rounded-xl px-4 py-3 text-sm flex items-center gap-2">
          <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.72 3h16.92a2 2 0 001.72-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
          You have {{ oldItems.length }} item(s) that have been in your wishlist for over 30 days.
        </div>

        <div v-if="wishlistItems.length"
             class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

          <div v-for="product in wishlistItems"
               :key="product.id"
               class="relative border rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition"
               :class="product.is_old ? 'border-red-200 bg-red-50 dark:bg-red-950/20' : 'border-gray-200 dark:border-zinc-700 bg-white dark:bg-zinc-800'">

            <!-- Old badge -->
            <div v-if="product.is_old"
                 class="absolute top-2 left-2 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-lg z-10">
              {{ product.days_in_wishlist }}d old
            </div>

            <!-- Remove button -->
            <button
              @click="removeItem(product)"
              class="absolute top-2 right-2 bg-white dark:bg-zinc-700 rounded-full p-1.5 shadow hover:bg-red-50 dark:hover:bg-red-900 transition z-10"
              title="Remove from wishlist"
            >
              <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>

            <img :src="product.thumbnail_url" class="h-48 w-full object-cover">

            <div class="p-4">
              <h2 class="font-semibold text-gray-900 dark:text-white">{{ product.name }}</h2>
              <p class="text-yellow-600 font-bold mt-1">KES {{ product.price }}</p>
              <p class="text-xs text-gray-400 mt-1">Added {{ formatDate(product.added_at) }}</p>

              <a :href="`/products/${product.id}`"
                 class="mt-3 block w-full text-center bg-yellow-400 hover:bg-yellow-500 text-black font-semibold text-sm py-2 rounded-xl transition">
                View Product
              </a>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-20 text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
          </svg>
          <p class="text-lg font-semibold text-gray-500">Your wishlist is empty</p>
          <p class="text-sm mt-1">Browse products and add some to your wishlist.</p>
          <a href="/products" class="mt-4 inline-block bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-2.5 rounded-xl transition">
            Shop Now
          </a>
        </div>
      </div>
    </ProfileLayout>
  </MainLayout>
</template>
