<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from './Layouts/MainLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

const page = usePage();

const policies = computed(() => page.props.policies || []);

const pageTitle = 'Returns & Refunds Policy';
const pageDescription = 'Learn about our return and refund policies at Buynow Kenya.';
</script>

<template>
  <MainLayout>
    <SeoHead :title="pageTitle" :description="pageDescription" />

  <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
          Returns & Refunds Policy
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          Our return and refund policies to ensure your satisfaction
        </p>
      </div>

      <!-- Policy Sections -->
      <div class="space-y-6">
        <div
          v-for="(policy, index) in policies"
          :key="index"
          class="bg-white dark:bg-zinc-800 rounded-lg shadow-md p-6"
        >
          <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ policy.title }}
          </h2>
          <p class="text-gray-600 dark:text-gray-400 leading-relaxed">
            {{ policy.description }}
          </p>
        </div>
      </div>

      <!-- Contact Section -->
      <div class="mt-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Need Help?
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          If you have any questions about our return policy, please contact our customer support team.
        </p>
        <div class="flex flex-col sm:flex-row gap-4">
          <a
            href="mailto:support@buynow.co.ke"
            class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>
            Email Support
          </a>
          <a
            href="tel:+254700000000"
            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
            </svg>
            Call Us
          </a>
        </div>
      </div>

      <!-- Return Request Button -->
      <div class="mt-8 text-center">
        <a
          href="/profile/orders"
          class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
        >
          Request a Return
        </a>
      </div>
    </div>
  </div>
  </MainLayout>
</template>