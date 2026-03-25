<script setup>
import { Link } from '@inertiajs/vue3';

const currentYear = new Date().getFullYear();

const quickLinks = [
  { name: 'Home', href: '/' },
  { name: 'Shop', href: '/products' },
  { name: 'Orders', href: '/profile/orders' },
  { name: 'Announcements', href: '/announcements' },
];

const customerService = [
  { name: 'Track Order', href: '/orders/track' },
  { name: 'Returns & Refunds', href: '/returns' },
  { name: 'Shipping Info', href: '/shipping' },
  { name: 'FAQ', href: '/faq' },
];

const contactInfo = {
  email: 'support@buynow.co.ke',
  phone: '+254 700 000 000',
  address: 'Nairobi, Kenya',
};

const socialLinks = [
  { name: 'Facebook', icon: 'facebook', url: '#' },
  { name: 'Twitter', icon: 'twitter', url: '#' },
  { name: 'Instagram', icon: 'instagram', url: '#' },
  { name: 'WhatsApp', icon: 'whatsapp', url: '#' },
];
</script>

<template>
  <footer class="bg-zinc-900 dark:bg-black text-zinc-300 dark:text-zinc-400">
    <!-- Main Footer Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
        
        <!-- Company Info -->
        <div class="space-y-4">
          <h3 class="text-xl font-bold text-white dark:text-white">
            Buynow
          </h3>
          <p class="text-sm leading-relaxed">
            Your trusted destination for premium electronics and gadgets in Kenya. 
            Fast delivery, secure payments via M-Pesa, and excellent customer service.
          </p>
          <div class="flex space-x-4 pt-2">
            <a 
              v-for="social in socialLinks" 
              :key="social.name"
              :href="social.url"
              class="text-zinc-400 hover:text-white dark:hover:text-white transition-colors duration-200"
              :aria-label="social.name"
            >
              <svg v-if="social.icon === 'facebook'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.77,7.46H14.5v-1.9c0-.9.6-1.1,1-1.1h3V.5h-4.33C10.24.5,9.5,3.44,9.5,5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4Z"/>
              </svg>
              <svg v-else-if="social.icon === 'twitter'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M23.95,4.57a10,10,0,0,1-2.82.77,4.96,4.96,0,0,0,2.16-2.72,9.9,9.9,0,0,1-3.12,1.19,4.92,4.92,0,0,0-8.39,4.49A14,14,0,0,1,1.64,3.16,4.92,4.92,0,0,0,3.2,9.72,4.86,4.86,0,0,1,.96,9.11v.06a4.93,4.93,0,0,0,3.95,4.83,4.86,4.86,0,0,1-2.22.08,4.93,4.93,0,0,0,4.6,3.42A9.87,9.87,0,0,1,0,19.54a13.94,13.94,0,0,0,7.55,2.21A13.9,13.9,0,0,0,21.56,7.68c0-.21,0-.42,0-.63A10,10,0,0,0,24,4.59Z"/>
              </svg>
              <svg v-else-if="social.icon === 'instagram'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12,2.16c3.2,0,3.58,0,4.85.07,3.25.15,4.77,1.69,4.92,4.92.06,1.27.07,1.65.07,4.85s0,3.58-.07,4.85c-.15,3.23-1.66,4.77-4.92,4.92-1.27.06-1.65.07-4.85.07s-3.58,0-4.85-.07c-3.26-.15-4.77-1.7-4.92-4.92-.06-1.27-.07-1.65-.07-4.85s0-3.58.07-4.85C2.38,3.92,3.9,2.38,7.15,2.23,8.42,2.18,8.8,2.16,12,2.16ZM12,0C8.74,0,8.33,0,7.05.07c-4.35.2-6.78,2.62-7,7C0,8.33,0,8.74,0,12s0,3.67.07,4.95c.2,4.36,2.62,6.78,7,7C8.33,24,8.74,24,12,24s3.67,0,4.95-.07c4.35-.2,6.78-2.62,7-7C24,15.67,24,15.26,24,12s0-3.67-.07-4.95c-.2-4.35-2.62-6.78-7-7C15.67,0,15.26,0,12,0Zm0,5.84A6.16,6.16,0,1,0,18.16,12,6.16,6.16,0,0,0,12,5.84ZM12,10a2,2,0,1,1,2-2A2,2,0,0,1,12,10ZM16.4,4.56a1.44,1.44,0,1,0,1.44,1.44A1.44,1.44,0,0,0,16.4,4.56Z"/>
              </svg>
              <svg v-else-if="social.icon === 'whatsapp'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M17.5,2A11.86,11.86,0,0,0,5.18,13.91l-.25.54a1.79,1.79,0,0,0,.66,2.07l.56.33a13.93,13.93,0,0,0,4.86,2.47,11.86,11.86,0,0,0,5.89,0l.56-.33a1.79,1.79,0,0,0,2.07-.66l.25-.54A11.86,11.86,0,0,0,17.5,2ZM12,21.5a9.87,9.87,0,0,1-5-1.37l-.36-.21-3.67,1,.36-.52a2.79,2.79,0,0,1-1.7-2.7,2.75,2.75,0,0,1,1-2l-.22-.56L7.71,11.6a9.87,9.87,0,0,1,4.29-8.38,9.86,9.86,0,0,1,9.89,9.86A9.87,9.87,0,0,1,12,21.5Zm5.27-6.58a8.4,8.4,0,0,0-5.24-2.7,8.4,8.4,0,0,0-5.24,2.7,8.4,8.4,0,0,0-2.7,5.24,8.4,8.4,0,0,0,2.7,5.24,8.4,8.4,0,0,0,5.24,2.7,8.4,8.4,0,0,0,5.24-2.7,8.4,8.4,0,0,0,2.7-5.24A8.4,8.4,0,0,0,17.27,14.92Zm-2.36,4.62a.72.72,0,0,1-.35.55,1.25,1.25,0,0,1-.66.21,1.16,1.16,0,0,1-.65-.21,2.16,2.16,0,0,1-.57-.55,4.17,4.17,0,0,1-.64-1.41,3.65,3.65,0,0,1-.16-1.31,1.3,1.3,0,0,1,.27-.85,1.13,1.13,0,0,1,.72-.46,1.17,1.17,0,0,1,.81.12,1.16,1.16,0,0,1,.45.77,3.15,3.15,0,0,0,.21.8.6.6,0,0,1,.12.38.55.55,0,0,1-.33.47A.55.55,0,0,1,14,18a.64.64,0,0,1-.46-.19,2.72,2.72,0,0,1-.38-.53,3.57,3.57,0,0,1-.52-1.5,3.73,3.73,0,0,1-.12-1.38,2.16,2.16,0,0,1,.43-1.2,1.89,1.89,0,0,1,1.12-.71,1.82,1.82,0,0,1,1.3.11,1.84,1.84,0,0,1,.77.92,3.47,3.47,0,0,1,.4,1.22,4.13,4.13,0,0,1,.07,1.19,3.86,3.86,0,0,1-.24,1.22,2.54,2.54,0,0,1-.62,1A2.58,2.58,0,0,1,14.91,19.54Z"/>
              </svg>
            </a>
          </div>
        </div>

        <!-- Quick Links -->
        <div class="space-y-4">
          <h4 class="text-lg font-semibold text-white dark:text-white">
            Quick Links
          </h4>
          <ul class="space-y-3">
            <li v-for="link in quickLinks" :key="link.name">
              <Link 
                :href="link.href"
                class="text-sm hover:text-white dark:hover:text-white transition-colors duration-200"
              >
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Customer Service -->
        <div class="space-y-4">
          <h4 class="text-lg font-semibold text-white dark:text-white">
            Customer Service
          </h4>
          <ul class="space-y-3">
            <li v-for="link in customerService" :key="link.name">
              <Link 
                :href="link.href"
                class="text-sm hover:text-white dark:hover:text-white transition-colors duration-200"
              >
                {{ link.name }}
              </Link>
            </li>
          </ul>
        </div>

        <!-- Contact Info -->
        <div class="space-y-4">
          <h4 class="text-lg font-semibold text-white dark:text-white">
            Contact Us
          </h4>
          <ul class="space-y-3">
            <li class="flex items-start space-x-3">
              <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
              </svg>
              <span class="text-sm">{{ contactInfo.email }}</span>
            </li>
            <li class="flex items-center space-x-3">
              <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
              </svg>
              <span class="text-sm">{{ contactInfo.phone }}</span>
            </li>
            <li class="flex items-start space-x-3">
              <svg class="w-5 h-5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              <span class="text-sm">{{ contactInfo.address }}</span>
            </li>
          </ul>
          
          <!-- Newsletter -->
          <div class="pt-4">
            <h5 class="text-sm font-medium text-white dark:text-white mb-2">
              Subscribe to our newsletter
            </h5>
            <form class="flex" @submit.prevent>
              <input
                type="email"
                placeholder="Enter your email"
                class="flex-1 px-4 py-2 bg-zinc-800 dark:bg-zinc-900 border border-zinc-700 dark:border-zinc-800 rounded-l-md text-sm text-white placeholder-zinc-500 focus:outline-none focus:border-blue-500 dark:focus:border-blue-500"
              />
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-r-md transition-colors duration-200"
              >
                Subscribe
              </button>
            </form>
          </div>
        </div>
      </div>

      <!-- Payment Methods & Bottom Bar -->
      <div class="mt-12 pt-8 border-t border-zinc-800 dark:border-zinc-800">
        <div class="flex flex-col md:flex-row justify-between items-center space-y-4 md:space-y-0">
          <!-- Payment Methods -->
          <div class="flex items-center space-x-4">
            <span class="text-sm text-zinc-400">We accept:</span>
            <div class="flex space-x-2">
              <span class="px-2 py-1 bg-zinc-800 dark:bg-zinc-900 text-xs font-medium rounded">M-Pesa</span>
              <span class="px-2 py-1 bg-zinc-800 dark:bg-zinc-900 text-xs font-medium rounded">Visa</span>
              <span class="px-2 py-1 bg-zinc-800 dark:bg-zinc-900 text-xs font-medium rounded">Mastercard</span>
            </div>
          </div>
          
          <!-- Copyright -->
          <div class="text-sm text-zinc-400">
            &copy; {{ currentYear }} Buynow. All rights reserved.
          </div>
        </div>
      </div>
    </div>
  </footer>
</template>