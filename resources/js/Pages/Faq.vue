<script setup>
import { ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import MainLayout from './Layouts/MainLayout.vue';
import SeoHead from '@/components/SeoHead.vue';

const page = usePage();

const faqs = ref(page.props.faqs || []);

// Accordion state
const openFaq = ref(null);

const toggleFaq = (index) => {
  openFaq.value = openFaq.value === index ? null : index;
};

const pageTitle = 'Frequently Asked Questions';
const pageDescription = 'Find answers to common questions about shopping at Buynow Kenya.';
</script>

<template>
  <MainLayout>
    <SeoHead :title="pageTitle" :description="pageDescription" />

  <div class="min-h-screen bg-gray-50 dark:bg-zinc-900 py-12">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white sm:text-4xl">
          Frequently Asked Questions
        </h1>
        <p class="mt-4 text-lg text-gray-600 dark:text-gray-400">
          Find answers to the most common questions about our services
        </p>
      </div>

      <!-- Search Box (optional enhancement) -->
      <div class="mb-8">
        <div class="relative">
          <input
            type="text"
            placeholder="Search for answers..."
            class="w-full px-4 py-3 pl-12 border border-gray-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-800 text-gray-900 dark:text-gray-100 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          />
          <svg class="absolute left-4 top-3.5 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
        </div>
      </div>

      <!-- FAQ Accordion -->
      <div class="space-y-4">
        <div
          v-for="(faq, index) in faqs"
          :key="index"
          class="bg-white dark:bg-zinc-800 rounded-lg shadow-md overflow-hidden"
        >
          <button
            @click="toggleFaq(index)"
            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 dark:hover:bg-zinc-700 transition-colors duration-200"
          >
            <span class="text-lg font-medium text-gray-900 dark:text-white">
              {{ faq.question }}
            </span>
            <svg
              class="w-5 h-5 text-gray-500 transform transition-transform duration-200"
              :class="{ 'rotate-180': openFaq === index }"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div
            v-show="openFaq === index"
            class="px-6 pb-4"
          >
            <p class="text-gray-600 dark:text-gray-400 leading-relaxed pt-2 border-t border-gray-200 dark:border-zinc-700">
              {{ faq.answer }}
            </p>
          </div>
        </div>
      </div>

      <!-- Contact Section -->
      <div class="mt-12 bg-blue-50 dark:bg-blue-900/20 rounded-lg p-6 text-center">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-3">
          Still Have Questions?
        </h3>
        <p class="text-gray-600 dark:text-gray-400 mb-4">
          Can't find what you're looking for? Our support team is here to help.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
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
            href="/contact"
            class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 dark:border-zinc-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-zinc-700 hover:bg-gray-50 dark:hover:bg-zinc-600"
          >
            Contact Us
          </a>
        </div>
      </div>
    </div>
  </div>
  </MainLayout>
</template>