<script setup>
import { computed } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import MainLayout from '../Layouts/MainLayout.vue';

const props = defineProps({
  store: Object,
  isAdmin: Boolean,
});

// Build Google Maps embed URL
const mapSrc = computed(() => {
  if (props.store.latitude && props.store.longitude) {
    return `https://maps.google.com/maps?q=${props.store.latitude},${props.store.longitude}&z=15&output=embed`;
  }
  const q = encodeURIComponent(
    [props.store.address, props.store.city, props.store.country].filter(Boolean).join(', ')
  );
  return `https://maps.google.com/maps?q=${q}&z=15&output=embed`;
});

const directionsUrl = computed(() => {
  if (props.store.latitude && props.store.longitude) {
    return `https://www.google.com/maps/dir/?api=1&destination=${props.store.latitude},${props.store.longitude}`;
  }
  const q = encodeURIComponent(
    [props.store.address, props.store.city, props.store.country].filter(Boolean).join(', ')
  );
  return `https://www.google.com/maps/search/?api=1&query=${q}`;
});

function deleteStore() {
  if (confirm('Are you sure you want to delete this store?')) {
    router.delete(`/stores/${props.store.id}`);
  }
}
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-5xl mx-auto">

        <!-- Back link -->
        <Link href="/stores" class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-yellow-600 mb-6 transition">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Back to Stores
        </Link>

        <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden">

          <!-- Header -->
          <div class="px-8 py-6 border-b border-gray-100 dark:border-zinc-800 flex items-start justify-between gap-4">
            <div>
              <div class="flex items-center gap-3 mb-1">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ store.name }}</h1>
                <span v-if="store.is_active"
                  class="text-xs font-bold bg-green-100 dark:bg-green-900 text-green-700 dark:text-green-300 px-2.5 py-0.5 rounded-full">
                  Open
                </span>
                <span v-else class="text-xs font-bold bg-red-100 dark:bg-red-900 text-red-600 dark:text-red-400 px-2.5 py-0.5 rounded-full">
                  Closed
                </span>
              </div>
              <p v-if="store.description" class="text-sm text-gray-500 dark:text-gray-400">{{ store.description }}</p>
            </div>

            <!-- Admin actions -->
            <div v-if="isAdmin" class="flex items-center gap-2 flex-shrink-0">
              <Link :href="`/stores/${store.id}/edit`"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-yellow-400 hover:bg-yellow-500 text-black text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
              </Link>
              <button @click="deleteStore"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Delete
              </button>
            </div>
          </div>

          <div class="grid grid-cols-1 lg:grid-cols-2 gap-0">

            <!-- Store Info -->
            <div class="px-8 py-6 space-y-5 border-b lg:border-b-0 lg:border-r border-gray-100 dark:border-zinc-800">

              <div v-if="store.address || store.city" class="flex gap-3">
                <div class="w-9 h-9 rounded-xl bg-yellow-100 dark:bg-yellow-900/30 flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Address</p>
                  <p class="text-gray-800 dark:text-gray-200 text-sm mt-0.5">
                    {{ [store.address, store.city, store.state, store.country, store.postal_code].filter(Boolean).join(', ') }}
                  </p>
                </div>
              </div>

              <div v-if="store.phone" class="flex gap-3">
                <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Phone</p>
                  <a :href="`tel:${store.phone}`" class="text-blue-600 hover:underline text-sm mt-0.5 block">{{ store.phone }}</a>
                </div>
              </div>

              <div v-if="store.email" class="flex gap-3">
                <div class="w-9 h-9 rounded-xl bg-purple-100 dark:bg-purple-900/30 flex items-center justify-center flex-shrink-0">
                  <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                </div>
                <div>
                  <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Email</p>
                  <a :href="`mailto:${store.email}`" class="text-purple-600 hover:underline text-sm mt-0.5 block">{{ store.email }}</a>
                </div>
              </div>

              <!-- Directions button -->
              <a :href="directionsUrl" target="_blank" rel="noopener"
                class="mt-4 inline-flex items-center gap-2 bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-5 py-2.5 rounded-xl text-sm transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                Get Directions
              </a>
            </div>

            <!-- Google Maps -->
            <div class="lg:p-0">
              <iframe
                :src="mapSrc"
                class="w-full h-72 lg:h-full min-h-[280px]"
                style="border:0;"
                allowfullscreen
                loading="lazy"
                referrerpolicy="no-referrer-when-downgrade"
                title="Store Location"
              ></iframe>
            </div>

          </div>
        </div>
      </div>
    </div>
  </MainLayout>
</template>
