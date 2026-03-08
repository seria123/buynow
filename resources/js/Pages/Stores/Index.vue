<script setup>
import { router, Link } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

const props = defineProps({
  stores: Array,
  isAdmin: Boolean,
});

function destroy(id) {
  if (confirm('Are you sure you want to delete this store?')) {
    router.delete(`/stores/${id}`);
  }
}
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-6xl mx-auto">

        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Our Stores</h1>
            <p class="text-sm text-gray-500 mt-1">Find a store near you</p>
          </div>
          <Link v-if="isAdmin" href="/stores/create"
            class="flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-5 py-2.5 rounded-xl text-sm transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Store
          </Link>
        </div>

        <div v-if="stores && stores.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <div v-for="store in stores" :key="store.id"
            class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-200 dark:border-zinc-800 shadow-sm hover:shadow-md transition overflow-hidden">

            <!-- Map preview -->
            <div class="h-40 bg-gray-100 dark:bg-zinc-800 overflow-hidden">
              <iframe
                :src="`https://maps.google.com/maps?q=${store.latitude && store.longitude ? store.latitude + ',' + store.longitude : encodeURIComponent([store.address, store.city, store.country].filter(Boolean).join(', '))}&z=14&output=embed`"
                class="w-full h-full pointer-events-none"
                style="border:0;"
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
              ></iframe>
            </div>

            <div class="p-5">
              <div class="flex items-center justify-between mb-1">
                <h2 class="font-bold text-gray-900 dark:text-white">{{ store.name }}</h2>
                <span v-if="store.is_active" class="text-xs font-semibold text-green-600 bg-green-100 dark:bg-green-900/40 px-2 py-0.5 rounded-full">Open</span>
                <span v-else class="text-xs font-semibold text-red-500 bg-red-100 dark:bg-red-900/40 px-2 py-0.5 rounded-full">Closed</span>
              </div>

              <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">
                {{ [store.city, store.country].filter(Boolean).join(', ') }}
              </p>

              <div class="flex items-center gap-2">
                <Link :href="`/stores/${store.id}`"
                  class="flex-1 text-center bg-yellow-400 hover:bg-yellow-500 text-black font-semibold text-xs py-2 rounded-xl transition">
                  View Details
                </Link>
                <template v-if="isAdmin">
                  <Link :href="`/stores/${store.id}/edit`"
                    class="p-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-800 transition" title="Edit">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                  </Link>
                  <button @click="destroy(store.id)"
                    class="p-2 rounded-xl border border-gray-200 dark:border-zinc-700 hover:bg-red-50 dark:hover:bg-red-900/20 transition" title="Delete">
                    <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                  </button>
                </template>
              </div>
            </div>
          </div>
        </div>

        <div v-else class="text-center py-20 text-gray-400">
          <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <p class="text-lg font-semibold text-gray-500">No stores found</p>
          <p v-if="isAdmin" class="text-sm mt-1">Click "Add Store" to get started.</p>
        </div>

      </div>
    </div>
  </MainLayout>
</template>
