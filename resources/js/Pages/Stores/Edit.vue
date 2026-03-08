<script setup>
import { router, Link } from '@inertiajs/vue3'
import { reactive } from 'vue'
import MainLayout from '../Layouts/MainLayout.vue'

const props = defineProps({
  store: Object,
})

const form = reactive({
  name:        props.store.name        ?? '',
  email:       props.store.email       ?? '',
  phone:       props.store.phone       ?? '',
  description: props.store.description ?? '',
  address:     props.store.address     ?? '',
  city:        props.store.city        ?? '',
  state:       props.store.state       ?? '',
  country:     props.store.country     ?? '',
  postal_code: props.store.postal_code ?? '',
  latitude:    props.store.latitude    ?? '',
  longitude:   props.store.longitude   ?? '',
  is_active:   props.store.is_active   ?? true,
})

function submit() {
  router.put(`/stores/${props.store.id}`, form)
}
</script>

<template>
  <MainLayout>
    <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8">
      <div class="max-w-3xl mx-auto">

        <div class="flex items-center justify-between mb-8">
          <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Edit Store</h1>
            <p class="text-sm text-gray-500 mt-1">Update the store details and location</p>
          </div>
          <Link href="/stores"
            class="text-sm text-gray-500 dark:text-gray-400 hover:text-yellow-500 transition">
            ← Back to Stores
          </Link>
        </div>

        <form @submit.prevent="submit" class="bg-white dark:bg-zinc-900 rounded-2xl shadow p-8 space-y-6">

          <!-- Basic Info -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Basic Information</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Store Name <span class="text-red-500">*</span></label>
                <input v-model="form.name" type="text" required
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                <input v-model="form.email" type="email"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Phone</label>
                <input v-model="form.phone" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Description</label>
                <textarea v-model="form.description" rows="3"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition"></textarea>
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-zinc-700" />

          <!-- Location -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Location</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Street Address</label>
                <input v-model="form.address" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">City</label>
                <input v-model="form.city" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">State / Province</label>
                <input v-model="form.state" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Country</label>
                <input v-model="form.country" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Postal Code</label>
                <input v-model="form.postal_code" type="text"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-zinc-700" />

          <!-- GPS Coordinates -->
          <div>
            <h2 class="text-lg font-semibold text-gray-800 dark:text-white mb-1">GPS Coordinates</h2>
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
              Enter the latitude and longitude to pin this store on the map.
              You can get coordinates from
              <a href="https://www.google.com/maps" target="_blank" class="text-yellow-500 underline">Google Maps</a>
              by right-clicking a location and selecting the coordinates.
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Latitude</label>
                <input v-model="form.latitude" type="number" step="any"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Longitude</label>
                <input v-model="form.longitude" type="number" step="any"
                  class="w-full px-4 py-2 rounded-xl border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-800 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 transition" />
              </div>
            </div>
          </div>

          <hr class="border-gray-200 dark:border-zinc-700" />

          <!-- Status -->
          <div class="flex items-center gap-3">
            <input v-model="form.is_active" type="checkbox" id="is_active"
              class="w-4 h-4 accent-yellow-400 rounded" />
            <label for="is_active" class="text-sm font-medium text-gray-700 dark:text-gray-300">
              Store is active and visible to customers
            </label>
          </div>

          <!-- Actions -->
          <div class="flex items-center gap-4 pt-2">
            <button type="submit"
              class="bg-yellow-400 hover:bg-yellow-500 text-black font-semibold px-6 py-2.5 rounded-xl text-sm transition">
              Update Store
            </button>
            <Link href="/stores" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 transition">
              Cancel
            </Link>
          </div>

        </form>
      </div>
    </div>
  </MainLayout>
</template>
