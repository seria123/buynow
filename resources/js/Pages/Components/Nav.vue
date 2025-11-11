<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const searchQuery = ref('');
const selectedCategory = ref('All Categories');
const showCategoryDropdown = ref(false);
const showMobileMenu = ref(false);
const showMobileSearch = ref(false);
const showUserMenu = ref(false);

const categories = [
    'All Categories',
    'TV & Audio',
    'Smart Phones',
    'Laptops & Desktops',
    'Gadgets',
    'GPS & Car',
    'Cameras & Accessories',
    'Movies & Games'
];

const navigation = [
    { name: 'Home', href: '#', hasDropdown: true },
    { name: 'TV & Audio', href: '#', hasDropdown: true },
    { name: 'Smart Phones', href: '#', hasDropdown: true },
    { name: 'Laptops & Desktops', href: '#', hasDropdown: true },
    { name: 'Gadgets', href: '#', hasDropdown: true },
    { name: 'GPS & Car', href: '#', hasDropdown: true },
    { name: 'Cameras & Accessories', href: '#', hasDropdown: true },
    { name: 'Movies & Games', href: '#', hasDropdown: true }
];

// Get logged-in user from Inertia props
const page = usePage();
const user = computed(() => page.props.auth?.user);

// Close user menu when clicking outside
const handleClickOutside = (event) => {
    if (showUserMenu.value && !event.target.closest('.user-menu-container')) {
        showUserMenu.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
});

// Get user initials for avatar fallback
const userInitials = computed(() => {
    if (!user.value?.name) return 'U';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .slice(0, 2);
});

// Handle logout
const handleLogout = () => {
    router.post('/logout');
};
</script>

<template>
    <div class="sticky top-0 z-40 shadow-lg">
        <!-- Top Bar -->
        <div
            class="bg-linear-to-r from-gray-900 via-black to-gray-900 text-gray-300 py-2.5 px-4 text-xs hidden md:block border-b border-yellow-400/20">
            <div class="max-w-7xl mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-400">Welcome to Buynow - All prices in KES</span>
                </div>
                <div class="flex items-center gap-6">
                    <a href="#" class="hover:text-yellow-400 transition-colors flex items-center gap-1.5 group">
                        <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span>Store Locator</span>
                    </a>
                    <a href="#" class="hover:text-yellow-400 transition-colors flex items-center gap-1.5 group">
                        <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <span>Track Order</span>
                    </a>
                    <div class="flex items-center gap-1.5 cursor-pointer hover:text-yellow-400 transition-colors">
                        <span>KES</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>
                    <div class="h-4 w-px bg-gray-700"></div>
                    <template v-if="user">
                        <!-- User Menu Dropdown -->
                        <div class="relative user-menu-container">
                            <button @click="showUserMenu = !showUserMenu"
                                class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-gray-800/50 transition-all duration-300 group">
                                <!-- User Avatar -->
                                <div class="relative">
                                    <div v-if="user.avatar"
                                        class="w-7 h-7 rounded-full bg-yellow-400 border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30">
                                        <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
                                    </div>
                                    <div v-else
                                        class="w-7 h-7 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20">
                                        <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=FFEB3B&color=222&size=64&rounded=true`"
                                            :alt="user.name" class="w-full h-full object-cover" />
                                    </div>
                                    <!-- Online indicator -->
                                    <span
                                        class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-green-400 border-2 border-gray-900 dark:border-gray-800 rounded-full shadow-sm"></span>
                                </div>
                                <!-- User Name (hidden on small screens) -->
                                <div class="hidden md:block text-left">
                                    <div class="text-xs text-gray-400 dark:text-gray-500">Welcome back</div>
                                    <div
                                        class="text-sm text-yellow-400 font-semibold leading-tight max-w-[120px] truncate">
                                        {{ user.name }}
                                    </div>
                                </div>
                                <!-- Dropdown Arrow -->
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 group-hover:text-yellow-400 transition-all duration-300 flex-shrink-0"
                                    :class="{ 'rotate-180': showUserMenu }" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Dropdown Menu -->
                            <transition enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0 scale-95 -translate-y-2"
                                enter-to-class="opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="opacity-100 scale-100 translate-y-0"
                                leave-to-class="opacity-0 scale-95 -translate-y-2">
                                <div v-if="showUserMenu"
                                    class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl shadow-2xl overflow-hidden z-50">
                                    <!-- User Info Header -->
                                    <div
                                        class="px-4 py-3 bg-gradient-to-br from-yellow-50 to-yellow-100 dark:from-gray-800 dark:to-gray-800/80 border-b border-yellow-200 dark:border-gray-700 rounded-t-xl">
                                        <div class="flex items-center gap-3">
                                            <div v-if="user.avatar"
                                                class="w-10 h-10 rounded-full bg-yellow-400 border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30">
                                                <img :src="user.avatar" :alt="user.name"
                                                    class="w-full h-full object-cover" />
                                            </div>
                                            <div v-else
                                                class="w-10 h-10 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20">
                                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=FFEB3B&color=222&size=64&rounded=true`"
                                                    :alt="user.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div
                                                    class="text-sm font-semibold text-gray-900 dark:text-white truncate">
                                                    {{ user.name }}
                                                </div>
                                                <div class="text-xs text-gray-600 dark:text-gray-400 truncate">{{
                                                    user.email }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Menu Items -->
                                    <div class="py-1 bg-white dark:bg-gray-800 rounded-b-xl">
                                        <Link href="/"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-gray-700/50 hover:text-yellow-600 dark:hover:text-yellow-400 transition-all duration-200 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                        </svg>
                                        <span>Dashboard</span>
                                        </Link>
                                        <Link href="/profile"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-gray-700/50 hover:text-yellow-600 dark:hover:text-yellow-400 transition-all duration-200 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        <span>My Profile</span>
                                        </Link>
                                        <Link href="/orders"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-gray-700/50 hover:text-yellow-600 dark:hover:text-yellow-400 transition-all duration-200 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span>My Orders</span>
                                        </Link>
                                        <Link href="/settings"
                                            class="flex items-center gap-3 px-4 py-2.5 text-sm text-gray-700 dark:text-gray-300 hover:bg-yellow-50 dark:hover:bg-gray-700/50 hover:text-yellow-600 dark:hover:text-yellow-400 transition-all duration-200 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        <span>Settings</span>
                                        </Link>
                                        <div class="border-t border-gray-200 dark:border-gray-700 my-1"></div>
                                        <button @click="handleLogout"
                                            class="w-full flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 hover:text-red-700 dark:hover:text-red-300 transition-all duration-200 group">
                                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                            </svg>
                                            <span>Sign Out</span>
                                        </button>
                                    </div>
                                </div>
                            </transition>
                        </div>
                    </template>
                    <template v-else>
                        <Link href="/register"
                            class="hover:text-yellow-400 transition-colors flex items-center gap-1.5 group">
                        <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                        </svg>
                        <span>Register</span>
                        </Link>
                        <Link href="/login" class="hover:text-yellow-400 transition-colors">Sign in</Link>
                    </template>
                </div>
            </div>
        </div>

        <!-- Main Header -->
        <div
            class="bg-linear-to-br from-yellow-400 via-yellow-300 to-yellow-400 backdrop-blur-lg py-3 md:py-4 px-4 border-b border-yellow-500/30">
            <div class="max-w-7xl mx-auto flex items-center gap-3 md:gap-6">
                <!-- Mobile Menu Button -->
                <button @click="showMobileMenu = !showMobileMenu"
                    class="p-2 hover:bg-black/10 rounded-xl transition-all duration-300 lg:hidden hover:scale-105 active:scale-95">
                    <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <a href="/"
                    class="text-3xl md:text-5xl font-extrabold text-transparent bg-clip-text bg-linear-to-r from-gray-900 via-gray-800 to-gray-900 tracking-tight hover:scale-105 transition-transform">
                    Buynow
                </a>

                <!-- Desktop Search Bar -->
                <div class="hidden lg:flex flex-1 max-w-3xl">
                    <div
                        class="flex w-full bg-white/95 backdrop-blur-xl rounded-full shadow-xl shadow-black/10 overflow-hidden border border-gray-200/50 hover:shadow-2xl hover:shadow-black/20 transition-all duration-300">
                        <input v-model="searchQuery" type="text" placeholder="Search for Products"
                            class="flex-1 px-6 py-3.5 focus:outline-none text-gray-800 placeholder-gray-400 bg-transparent" />
                        <div class="relative">
                            <button @click="showCategoryDropdown = !showCategoryDropdown"
                                class="px-4 py-3.5 text-gray-700 flex items-center gap-2 hover:bg-gray-50/80 transition-all duration-300 whitespace-nowrap border-l border-gray-200/50 group">
                                <span class="hidden xl:inline text-sm font-medium">{{ selectedCategory }}</span>
                                <svg class="w-4 h-4 group-hover:rotate-180 transition-transform duration-300"
                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>

                            <!-- Category Dropdown -->
                            <transition enter-active-class="transition-all duration-200 ease-out"
                                enter-from-class="opacity-0 scale-95 -translate-y-2"
                                enter-to-class="opacity-100 scale-100 translate-y-0"
                                leave-active-class="transition-all duration-150 ease-in"
                                leave-from-class="opacity-100 scale-100 translate-y-0"
                                leave-to-class="opacity-0 scale-95 -translate-y-2">
                                <div v-if="showCategoryDropdown"
                                    class="absolute top-full right-0 mt-2 bg-white backdrop-blur-xl border border-gray-200 rounded-2xl shadow-2xl z-50 min-w-[220px] overflow-hidden">
                                    <button v-for="category in categories" :key="category"
                                        @click="selectedCategory = category; showCategoryDropdown = false"
                                        class="w-full px-4 py-3 text-left hover:bg-yellow-50 transition-all duration-200 text-gray-800 text-sm font-medium hover:pl-6"
                                        :class="selectedCategory === category ? 'bg-yellow-100 text-yellow-700' : ''">
                                        {{ category }}
                                    </button>
                                </div>
                            </transition>
                        </div>
                        <button
                            class="px-6 py-3.5 bg-linear-to-r from-gray-900 to-black text-white hover:from-black hover:to-gray-900 transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Right Actions -->
                <div class="flex items-center gap-1.5 md:gap-3 ml-auto">
                    <!-- Mobile Search Toggle -->
                    <button @click="showMobileSearch = !showMobileSearch"
                        class="p-2.5 hover:bg-black/10 rounded-xl transition-all duration-300 lg:hidden hover:scale-105 active:scale-95">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <button
                        class="p-2.5 hover:bg-black/10 rounded-xl transition-all duration-300 hidden md:flex hover:scale-105 active:scale-95 group">
                        <svg class="w-5 h-5 text-gray-900 group-hover:rotate-180 transition-transform duration-500"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <button
                        class="p-2.5 hover:bg-black/10 rounded-xl transition-all duration-300 hidden md:flex hover:scale-105 active:scale-95 group relative">
                        <svg class="w-5 h-5 text-gray-900 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    <button
                        class="relative p-2.5 hover:bg-black/10 rounded-xl transition-all duration-300 hover:scale-105 active:scale-95 group">
                        <svg class="w-5 h-5 text-gray-900 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-linear-to-br from-gray-900 to-black text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-lg animate-pulse">
                            2
                        </span>
                    </button>
                    <div class="text-right hidden md:block ml-2">
                        <div class="text-[10px] text-gray-700 font-medium">Your Cart</div>
                        <div class="text-lg font-bold text-gray-900 tracking-tight">KES 1,785</div>
                    </div>
                </div>
            </div>

            <!-- Mobile Search Bar -->
            <transition enter-active-class="transition-all duration-300 ease-out"
                enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0"
                leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0"
                leave-to-class="opacity-0 -translate-y-2">
                <div v-if="showMobileSearch" class="mt-3 lg:hidden">
                    <div
                        class="flex bg-white/95 backdrop-blur-xl rounded-full shadow-xl overflow-hidden border border-gray-200/50">
                        <input v-model="searchQuery" type="text" placeholder="Search for Products"
                            class="flex-1 px-5 py-3 focus:outline-none text-gray-800 text-sm bg-transparent" />
                        <button
                            class="px-5 py-3 bg-linear-to-r from-gray-900 to-black text-white hover:from-black hover:to-gray-900 transition-all duration-300">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </transition>
        </div>

        <!-- Desktop Navigation Menu -->
        <div class="bg-white/95 backdrop-blur-xl border-b border-gray-100 shadow-sm hidden lg:block">
            <div class="max-w-7xl mx-auto">
                <nav class="flex items-center justify-center">
                    <a v-for="item in navigation" :key="item.name" :href="item.href"
                        class="px-5 py-4 text-gray-700 hover:text-gray-900 hover:bg-yellow-50 transition-all duration-300 flex items-center gap-2 font-medium text-sm relative group">
                        <span>{{ item.name }}</span>
                        <svg v-if="item.hasDropdown"
                            class="w-4 h-4 group-hover:rotate-180 transition-transform duration-300" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        <span
                            class="absolute bottom-0 left-0 w-0 h-0.5 bg-yellow-400 group-hover:w-full transition-all duration-300"></span>
                    </a>
                </nav>
            </div>
        </div>

        <!-- Mobile Menu Sidebar -->
        <transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0"
            enter-to-class="opacity-100" leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100" leave-to-class="opacity-0">
            <div v-if="showMobileMenu" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 lg:hidden"
                @click="showMobileMenu = false">
                <transition enter-active-class="transition-all duration-300 ease-out"
                    enter-from-class="-translate-x-full" enter-to-class="translate-x-0"
                    leave-active-class="transition-all duration-200 ease-in" leave-from-class="translate-x-0"
                    leave-to-class="-translate-x-full">
                    <div v-if="showMobileMenu" class="w-80 max-w-[85vw] h-full bg-white shadow-2xl overflow-y-auto"
                        @click.stop>
                        <!-- Mobile Menu Header -->
                        <div
                            class="bg-linear-to-br from-yellow-400 via-yellow-300 to-yellow-400 p-5 flex items-center justify-between border-b border-yellow-500/30">
                            <span class="text-2xl font-bold text-gray-900">Menu</span>
                            <button @click="showMobileMenu = false"
                                class="p-2 hover:bg-black/10 rounded-xl transition-all duration-300 hover:rotate-90">
                                <svg class="w-6 h-6 text-gray-900" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <!-- Mobile Menu Items -->
                        <div class="p-4">
                            <!-- User Actions -->
                            <div class="mb-6 pb-6 border-b border-gray-100">
                                <template v-if="user">
                                    <!-- User Info Card -->
                                    <div
                                        class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 mb-4 border border-yellow-200 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div v-if="user.avatar"
                                                class="w-12 h-12 rounded-full bg-yellow-400 border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shrink-0">
                                                <img :src="user.avatar" :alt="user.name"
                                                    class="w-full h-full object-cover" />
                                            </div>
                                            <div v-else
                                                class="w-12 h-12 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20">
                                                <img :src="`https://ui-avatars.com/api/?name=${encodeURIComponent(user.name)}&background=FFEB3B&color=222&size=64&rounded=true`"
                                                    :alt="user.name" class="w-full h-full object-cover" />
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <div class="text-sm font-bold text-gray-900 truncate">{{ user.name }}
                                                </div>
                                                <div class="text-xs text-gray-600 truncate">{{ user.email }}</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- User Menu Items -->
                                    <Link href="/"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-600 hover:bg-yellow-50 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span class="font-medium">Dashboard</span>
                                    </Link>
                                    <Link href="/profile"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-600 hover:bg-yellow-50 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">My Profile</span>
                                    </Link>
                                    <Link href="/orders"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-600 hover:bg-yellow-50 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="font-medium">My Orders</span>
                                    </Link>
                                    <Link href="/settings"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-600 hover:bg-yellow-50 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-medium">Settings</span>
                                    </Link>
                                    <div class="border-t border-gray-200 my-2"></div>
                                    <button @click="handleLogout"
                                        class="w-full flex items-center gap-3 py-3 px-4 text-red-600 hover:text-red-700 hover:bg-red-50 rounded-xl transition-all duration-300 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        <span class="font-medium">Sign Out</span>
                                    </button>
                                </template>
                                <template v-else>
                                    <Link href="/register"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-500 hover:bg-yellow-50 rounded-xl transition-all duration-300 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">Register</span>
                                    </Link>
                                    <Link href="/login"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-500 hover:bg-yellow-50 rounded-xl transition-all duration-300 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-medium">Sign in</span>
                                    </Link>
                                    <a href="#"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 hover:text-yellow-500 hover:bg-yellow-50 rounded-xl transition-all duration-300 group">
                                        <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <span class="font-medium">Track Your Order</span>
                                    </a>
                                </template>
                            </div>

                            <!-- Navigation Links -->
                            <div class="space-y-1">
                                <a v-for="item in navigation" :key="item.name" :href="item.href"
                                    class="flex items-center justify-between py-3.5 px-4 text-gray-700 hover:bg-yellow-50 hover:text-yellow-600 rounded-xl transition-all duration-300 font-medium group">
                                    <span>{{ item.name }}</span>
                                    <svg v-if="item.hasDropdown"
                                        class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                </transition>
            </div>
        </transition>
    </div>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>
