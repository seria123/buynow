<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from './Layouts/MainLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

const page = usePage();

const shippingInfo = computed(() => page.props.shippingInfo || {});

const deliveryTimes = computed(() => shippingInfo.value.deliveryTimes || []);
const shippingMethods = computed(() => shippingInfo.value.shippingMethods || []);
const freeShippingThreshold = computed(() => shippingInfo.value.freeShippingThreshold || 10000);
const orderTracking = computed(() => shippingInfo.value.orderTracking || '');

const pageTitle = 'Shipping Information';
const pageDescription = 'Learn about our shipping options, delivery times, and costs at Buynow Kenya.';
</script>

<template>
  <MainLayout>
    <SeoHead :title="pageTitle" :description="pageDescription" />

  <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
          Shipping Information
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          Everything you need to know about delivery options
        </p>
      </div>

      <!-- Free Shipping Banner -->
      <div class="mb-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-lg p-6 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-xl font-bold">Free Shipping Available!</h2>
            <p class="mt-1">Orders over KSh {{ freeShippingThreshold.toLocaleString() }} qualify for free shipping</p>
          </div>
          <svg class="w-12 h-12 opacity-75" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
          </svg>
        </div>
      </div>

      <!-- Delivery Times Table -->
      <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          Delivery Times by Location
        </h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200 dark:divide-zinc-700">
            <thead class="bg-gray-50 dark:bg-zinc-700">
              <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Location
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Delivery Time
                </th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">
                  Cost
                </th>
              </tr>
            </thead>
            <tbody class="bg-white dark:bg-zinc-800 divide-y divide-gray-200 dark:divide-zinc-700">
              <tr v-for="(delivery, index) in deliveryTimes" :key="index">
                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-300">
                  {{ delivery.location }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                  {{ delivery.time }}
                </td>
                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                  {{ delivery.cost }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Shipping Methods -->
      <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
          </svg>
          Shipping Methods
        </h2>
        <div class="grid gap-4 md:grid-cols-3">
          <div
            v-for="(method, index) in shippingMethods"
            :key="index"
            class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4"
          >
            <h3 class="font-medium text-gray-900 dark:text-white">{{ method.name }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ method.description }}</p>
          </div>
        </div>
      </div>

      <!-- Order Tracking -->
      <div class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
          <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
          </svg>
          Order Tracking
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
          {{ orderTracking }}
        </p>
        <div class="mt-4">
          <a
            href="/orders/track"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            Track Your Order
          </a>
        </div>
      </div>

      <!-- Contact Section -->
      <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Shipping Questions?
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Contact our shipping team for any delivery-related inquiries.
        </p>
        <a
          href="mailto:support@buynow.co.ke"
          class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
        >
          Email Shipping Support
        </a>
      </div>
    </div>
  </div>
  </MainLayout>
</template>