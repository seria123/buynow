<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import { useCart } from '../../cart.js';

const page = usePage();
// Make user reactive so it updates when page data changes
const user = computed(() => page.props.auth?.user);
const { cartCount, loadCart } = useCart();

// load cart when sidebar mounts
onMounted(() => {
    loadCart();
});
const isActive = (url) => page.url === url;

const navItems = [
    { name: 'Account Overview', href: '/profile', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
    { name: 'Edit Profile', href: '/profile/edit', icon: 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z' },
    {name: 'Cart',href: '/cart/page',icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z', },
    { name: 'My Orders', href: '/profile/orders', icon: 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z' },
    { name: 'Wishlist', href: '/profile/wishlist', icon: 'M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z' },
    { name: 'Stores', href: '/stores', icon: 'M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z' },
    { name: 'Announcements', href: '/announcements', icon: 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9' },
    { name: 'Promotions', href: '/promotions', icon: 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z' },
    { name: 'Security', href: '/profile/security', icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z' },
    { name: 'Settings', href: '/profile/settings', icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z' },
];
</script>

<template>
    <MainLayout>
        <div class="min-h-screen bg-gray-50 dark:bg-zinc-950 py-12 px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex flex-col lg:flex-row gap-8">
                
                <aside class="w-full lg:w-72 flex-shrink-0 space-y-4">
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl p-6 border border-gray-200 dark:border-zinc-800 shadow-sm text-center">
<img 
  :src="user?.avatar || `https://ui-avatars.com/api/?name=${user?.name || 'User'}`" 
  class="w-24 h-24 rounded-full border-4 border-yellow-400 p-1 shadow-xl mx-auto object-cover" 
/>
                        <h2 class="mt-4 font-bold text-gray-900 dark:text-white text-lg">{{ user?.name }}</h2>
                        <p class="text-xs text-gray-500 uppercase tracking-wider">{{ user?.username }}</p>
                    </div>

                    <nav class="bg-white dark:bg-zinc-900 rounded-3xl p-4 border border-gray-200 dark:border-zinc-800 shadow-sm space-y-1">
                        <Link v-for="item in navItems" :key="item.name" :href="item.href"
                            class="flex items-center gap-3 px-4 py-3 rounded-2xl transition-all duration-300 group"
                            :class="isActive(item.href) 
                                ? 'bg-yellow-400 text-black shadow-lg shadow-yellow-400/20' 
                                : 'text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-zinc-800'">
                            
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            <span class="font-semibold text-sm">{{ item.name }}</span>
                            <div v-if="isActive(item.href)" class="ml-auto w-1.5 h-1.5 bg-black rounded-full"></div>
                        </Link>
                    </nav>
                </aside>

                <main class="flex-1 min-w-0">
                    <div class="bg-white dark:bg-zinc-900 rounded-3xl border border-gray-200 dark:border-zinc-800 shadow-sm overflow-hidden p-6 lg:p-10">
                        <slot />
                    </div>
                </main>
            </div>
        </div>
    </MainLayout>
</template>