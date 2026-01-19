<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';

const props = defineProps({
    isDark: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle-dark-mode']);

const searchQuery = ref('');
const selectedCategory = ref('All Categories');
const showCategoryDropdown = ref(false);
const showMobileMenu = ref(false);
const showMobileSearch = ref(false);
const showUserMenu = ref(false);
const dropdownCategoryId = ref(null);
let dropdownCloseTimeout = null;
const flattenedTreeCache = new WeakMap();

// Get logged-in user and categories from Inertia props
const page = usePage();
const user = computed(() => page.props.auth?.user);
const dbCategories = computed(() => page.props.categories || []);

// Build categories array with "All Categories" option
const categories = computed(() => {
    return [
        { name: 'All Categories', slug: 'all', id: null },
        ...dbCategories.value.map(cat => ({
            name: cat.name,
            slug: cat.slug,
            id: cat.id
        }))
    ];
});

// Build navigation array from categories
const categoryHref = (slug) => {
    if (!slug || slug === 'all') {
        return '/products';
    }

    return `/products?category=${encodeURIComponent(slug)}`;
};

const navigation = computed(() => [
    { name: 'Home', href: '/', hasDropdown: false },
    { name: 'Products', href: '/products', hasDropdown: false },
    ...dbCategories.value.map((cat) => ({
        name: cat.name,
        href: categoryHref(cat.slug),
        hasDropdown: true,
        category: cat,
    })),
]);

const currentPath = computed(() => {
    const path = page.url?.split('?')[0];

    return path && path.length ? path : '/';
});

const currentCategorySlug = computed(() => {
    const url = page.url || '';
    const urlObj = new URL(url, window.location.origin);
    const categoryParam = urlObj.searchParams.get('category');

    return categoryParam;
});

const currentCategorySlugs = computed(() => {
    const url = page.url || '';
    const urlObj = new URL(url, window.location.origin);
    const categoriesParam = urlObj.searchParams.getAll('categories[]');

    // Support both single 'category' param and multiple 'categories[]' params
    const singleCategory = urlObj.searchParams.get('category');
    const categories = categoriesParam.length > 0 ? categoriesParam : (singleCategory ? [singleCategory] : []);

    return categories;
});

const isNavItemActive = (item) => {
    if (!item?.href) {
        return false;
    }

    if (item.href === '/') {
        return currentPath.value === '/';
    }

    // For "Products" nav item, check if we're on /products without any category filter
    if (item.name === 'Products' && item.href === '/products') {
        return currentPath.value === '/products' && currentCategorySlugs.value.length === 0;
    }

    // For category nav items, check if the category slug matches any of the current category filters
    if (item?.hasDropdown && item?.category) {
        const currentCategories = currentCategorySlugs.value;
        // Return true if this nav item's category is in the current category filters
        // For multiple categories, use first match (per user preference)
        return currentCategories.length > 0 && currentCategories.includes(item.category.slug);
    }

    // Fallback to path matching
    return currentPath.value.startsWith(item.href);
};

const currencyFormatter = new Intl.NumberFormat('en-KE', {
    style: 'currency',
    currency: 'KES',
    maximumFractionDigits: 0,
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '—';
    }

    return currencyFormatter.format(Number(value));
};

const cancelDropdownClose = () => {
    if (dropdownCloseTimeout) {
        clearTimeout(dropdownCloseTimeout);
        dropdownCloseTimeout = null;
    }
};

const scheduleDropdownClose = () => {
    cancelDropdownClose();
    dropdownCloseTimeout = setTimeout(() => {
        dropdownCategoryId.value = null;
    }, 300);
};

const handleNavMouseEnter = (item) => {
    if (!item?.hasDropdown || !item?.category) {
        dropdownCategoryId.value = null;

        return;
    }

    cancelDropdownClose();
    dropdownCategoryId.value = item.category.id;
};

const handleNavMouseLeave = (item) => {
    if (!item?.hasDropdown || !item?.category) {
        return;
    }

    // Don't close immediately when leaving nav item - allow time to move to dropdown
    // The close will be scheduled when leaving the dropdown itself
};

const flattenCategoryTree = (category) => {
    if (!category) {
        return [];
    }

    if (flattenedTreeCache.has(category)) {
        return flattenedTreeCache.get(category);
    }

    const nodes = [];
    const traverse = (node, depth = 0) => {
        nodes.push({ category: node, depth });
        (node.children ?? []).forEach((child) => traverse(child, depth + 1));
    };

    traverse(category);
    flattenedTreeCache.set(category, nodes);

    return nodes;
};

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
    cancelDropdownClose();
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
            class="bg-gray-900 dark:bg-zinc-950 text-gray-300 dark:text-gray-400 py-2.5 px-4 text-xs hidden md:block border-b border-yellow-400/20 dark:border-zinc-900">
            <div class="container mx-auto flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-3.5 h-3.5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M5 2a1 1 0 011 1v1h1a1 1 0 010 2H6v1a1 1 0 01-2 0V6H3a1 1 0 010-2h1V3a1 1 0 011-1zm0 10a1 1 0 011 1v1h1a1 1 0 110 2H6v1a1 1 0 11-2 0v-1H3a1 1 0 110-2h1v-1a1 1 0 011-1zM12 2a1 1 0 01.967.744L14.146 7.2 17.5 9.134a1 1 0 010 1.732l-3.354 1.935-1.18 4.455a1 1 0 01-1.933 0L9.854 12.8 6.5 10.866a1 1 0 010-1.732l3.354-1.935 1.18-4.455A1 1 0 0112 2z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="text-gray-400">Welcome to Buynow - All prices in KES</span>
                </div>
                <div class="flex items-center gap-6">
                    <!-- Dark Mode Toggle -->
                    <button @click="emit('toggle-dark-mode')"
                        class="hover:text-yellow-400 transition-colors flex items-center gap-1.5 group">
                        <svg v-if="!isDark" class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                        <svg v-else class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span>{{ isDark ? 'Light' : 'Dark' }}</span>
                    </button>
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
                                    <div
                                        class="w-7 h-7 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20">
                                        <img :src="user.avatar" :alt="user.name" class="w-full h-full object-cover" />
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
                                <svg class="w-3.5 h-3.5 text-gray-400 dark:text-gray-500 group-hover:text-yellow-400 transition-all duration-300 shrink-0"
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
                                    class="absolute right-0 mt-2 w-64 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-md shadow-2xl overflow-hidden z-50">
                                    <!-- User Info Header -->
                                    <div
                                        class="px-4 py-3 bg-linear-to-br from-yellow-50 to-yellow-100 dark:from-gray-800 dark:to-gray-800/80 border-b border-yellow-200 dark:border-gray-700 rounded-t-md">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-10 h-10 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20">
                                                <img :src="user.avatar" :alt="user.name"
                                                    class="w-full h-full object-cover" />
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
                                    <div class="py-1 bg-white dark:bg-gray-800 rounded-b-md">
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
            class="bg-linear-to-br from-yellow-400 via-yellow-300 to-yellow-400 dark:bg-zinc-900 backdrop-blur-lg py-3 md:py-4 px-4 border-b border-yellow-500/30 dark:border-zinc-800 transition-colors duration-300 relative overflow-hidden">
            <!-- Subtle Tech Pattern Overlay -->
            <div class="absolute inset-0 opacity-[0.15] dark:opacity-[0.08] pointer-events-none">
                <svg class="w-full h-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="tech-pattern" x="0" y="0" width="120" height="120" patternUnits="userSpaceOnUse">
                            <!-- Digital grid lines -->
                            <line x1="0" y1="40" x2="120" y2="40" stroke="currentColor" stroke-width="0.5"
                                opacity="0.3" />
                            <line x1="0" y1="80" x2="120" y2="80" stroke="currentColor" stroke-width="0.5"
                                opacity="0.3" />
                            <line x1="40" y1="0" x2="40" y2="120" stroke="currentColor" stroke-width="0.5"
                                opacity="0.3" />
                            <line x1="80" y1="0" x2="80" y2="120" stroke="currentColor" stroke-width="0.5"
                                opacity="0.3" />

                            <!-- Grid dots at intersections -->
                            <circle cx="40" cy="40" r="2" fill="currentColor" opacity="0.4" />
                            <circle cx="80" cy="40" r="2" fill="currentColor" opacity="0.4" />
                            <circle cx="40" cy="80" r="2" fill="currentColor" opacity="0.4" />
                            <circle cx="80" cy="80" r="2" fill="currentColor" opacity="0.4" />

                            <!-- Tech hexagons -->
                            <polygon points="20,15 30,10 40,15 40,25 30,30 20,25" stroke="currentColor" stroke-width="1"
                                fill="none" opacity="0.5" />
                            <polygon points="90,55 100,50 110,55 110,65 100,70 90,65" stroke="currentColor"
                                stroke-width="1" fill="none" opacity="0.5" />
                            <polygon points="55,95 65,90 75,95 75,105 65,110 55,105" stroke="currentColor"
                                stroke-width="1" fill="none" opacity="0.5" />

                            <!-- Signal waves (simplified) -->
                            <path d="M0 20 Q10 15 20 20 T40 20" stroke="currentColor" stroke-width="1.5" fill="none"
                                opacity="0.3" />
                            <path d="M80 100 Q90 95 100 100 T120 100" stroke="currentColor" stroke-width="1.5"
                                fill="none" opacity="0.3" />

                            <!-- Small squares (pixels/chips) -->
                            <rect x="10" y="70" width="8" height="8" stroke="currentColor" stroke-width="1" fill="none"
                                opacity="0.4" />
                            <rect x="100" y="25" width="8" height="8" stroke="currentColor" stroke-width="1" fill="none"
                                opacity="0.4" />
                            <rect x="65" y="15" width="6" height="6" stroke="currentColor" stroke-width="1" fill="none"
                                opacity="0.4" />

                            <!-- Corner accents -->
                            <polyline points="0,0 15,0 15,15" stroke="currentColor" stroke-width="1.5" fill="none"
                                opacity="0.3" />
                            <polyline points="120,120 105,120 105,105" stroke="currentColor" stroke-width="1.5"
                                fill="none" opacity="0.3" />
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#tech-pattern)"
                        class="text-gray-900 dark:text-yellow-500" />
                </svg>
            </div>

            <div class="container mx-auto flex items-center gap-3 md:gap-6 relative z-10">
                <!-- Mobile Menu Button -->
                <button @click="showMobileMenu = !showMobileMenu"
                    class="p-2 hover:bg-black/10 dark:hover:bg-white/10 rounded-xl transition-all duration-300 lg:hidden hover:scale-105 active:scale-95">
                    <svg class="w-6 h-6 text-gray-900 dark:text-white" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                <!-- Logo -->
                <Link href="/"
                    class="text-4xl font-extrabold text-gray-900 tracking-tight hover:scale-105 transition-transform shrink-0">
                Buynow
                </Link>

                <!-- Desktop Search Bar -->
                <div
                    class="hidden lg:flex absolute left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-[600px] xl:max-w-[700px] 2xl:max-w-[800px] px-2">
                    <div
                        class="flex w-full bg-white/95 dark:bg-zinc-800/90 backdrop-blur-xl rounded-full shadow-xl shadow-black/10 dark:shadow-black/30 overflow-hidden border border-gray-200/50 dark:border-zinc-700/50 hover:shadow-2xl hover:shadow-black/20 dark:hover:shadow-black/40 transition-all duration-300">
                        <input v-model="searchQuery" type="text" placeholder="Search for Products"
                            class="flex-1 px-6 py-3.5 focus:outline-none text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 bg-transparent min-w-0" />
                        <div class="relative">
                            <button @click="showCategoryDropdown = !showCategoryDropdown"
                                class="px-4 py-3.5 text-gray-700 dark:text-gray-300 flex items-center gap-2 hover:bg-gray-50/80 dark:hover:bg-zinc-700/50 transition-all duration-300 whitespace-nowrap border-l border-gray-200/50 dark:border-zinc-700/50 group">
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
                                    class="absolute top-full right-0 mt-2 bg-white dark:bg-zinc-800 backdrop-blur-xl border border-gray-200 dark:border-zinc-700 rounded-2xl shadow-2xl z-50 min-w-[220px] overflow-hidden">
                                    <Link v-for="category in categories" :key="category.slug || category.id"
                                        :href="categoryHref(category.slug)"
                                        @click="selectedCategory = category.name; showCategoryDropdown = false"
                                        class="w-full px-4 py-3 text-left hover:bg-yellow-50 dark:hover:bg-yellow-900/20 transition-all duration-200 text-gray-800 dark:text-gray-200 text-sm font-medium hover:pl-6 block"
                                        :class="selectedCategory === category.name ? 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400' : ''">
                                    {{ category.name }}
                                    </Link>
                                </div>
                            </transition>
                        </div>
                        <button
                            class="px-6 py-3.5 bg-linear-to-r from-gray-900 to-black dark:from-yellow-600 dark:to-yellow-700 text-white hover:from-black hover:to-gray-900 dark:hover:from-yellow-700 dark:hover:to-yellow-800 transition-all duration-300 hover:scale-105 active:scale-95 flex items-center justify-center">
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
                        class="p-2.5 hover:bg-black/10 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 lg:hidden hover:scale-105 active:scale-95">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </button>

                    <button
                        class="p-2.5 hover:bg-black/10 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 hidden md:flex hover:scale-105 active:scale-95 group">
                        <svg class="w-5 h-5 text-gray-900 group-hover:rotate-180 transition-transform duration-500"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                    <button
                        class="p-2.5 hover:bg-black/10 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 hidden md:flex hover:scale-105 active:scale-95 group relative">
                        <svg class="w-5 h-5 text-gray-900 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </button>
                    <button
                        class="relative p-2.5 hover:bg-black/10 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 hover:scale-105 active:scale-95 group">
                        <svg class="w-5 h-5 text-gray-900 group-hover:scale-110 transition-transform" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                        </svg>
                        <span
                            class="absolute -top-1 -right-1 bg-linear-to-br from-gray-900 to-black dark:from-yellow-500 dark:to-yellow-600 text-white dark:text-gray-900 text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold shadow-lg animate-pulse">
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
                        class="flex bg-white/95 dark:bg-zinc-800/90 backdrop-blur-xl rounded-full shadow-xl overflow-hidden border border-gray-200/50 dark:border-zinc-700/50">
                        <input v-model="searchQuery" type="text" placeholder="Search for Products"
                            class="flex-1 px-5 py-3 focus:outline-none text-gray-800 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 text-sm bg-transparent" />
                        <button
                            class="px-5 py-3 bg-linear-to-r from-gray-900 to-black dark:from-yellow-600 dark:to-yellow-700 text-white hover:from-black hover:to-gray-900 dark:hover:from-yellow-700 dark:hover:to-yellow-800 transition-all duration-300">
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
        <div
            class="bg-white/95 dark:bg-zinc-900/95 backdrop-blur-xl border-b border-gray-100 dark:border-zinc-800 shadow-sm hidden lg:block relative">
            <div class="container mx-auto">
                <nav class="flex items-center justify-center gap-3">
                    <template v-for="(item, index) in navigation" :key="item.name">
                        <div class="group/nav" @mouseenter="handleNavMouseEnter(item)"
                            @mouseleave="handleNavMouseLeave(item)">
                            <Link :href="item.href"
                                class="py-4 px-4 text-sm font-medium flex items-center gap-1.5 transition-all duration-300 relative"
                                :class="(isNavItemActive(item) || dropdownCategoryId === item.category?.id) ? 'text-yellow-600 dark:text-yellow-400' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white'">
                            <span>{{ item.name }}</span>
                            <svg v-if="item.hasDropdown" class="w-4 h-4 transition-transform duration-300"
                                :class="(isNavItemActive(item) || dropdownCategoryId === item.category?.id) ? 'text-yellow-500 rotate-180' : 'text-gray-400 group-hover/nav:text-gray-600 dark:group-hover/nav:text-gray-300'"
                                fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>

                            <!-- Active Indicator -->
                            <span
                                class="absolute bottom-0 left-0 right-0 h-0.5 bg-yellow-500 dark:bg-yellow-400 transform origin-left transition-transform duration-300"
                                :class="isNavItemActive(item) ? 'scale-x-100' : 'scale-x-0 group-hover/nav:scale-x-100'"></span>
                            </Link>

                            <!-- Dropdown Menu -->
                            <transition enter-active-class="transition duration-200 ease-out"
                                enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0"
                                leave-active-class="transition duration-300 ease-in"
                                leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
                                <div v-if="item.category && dropdownCategoryId === item.category.id"
                                    class="absolute left-0 top-full w-full z-40" @mouseenter="cancelDropdownClose"
                                    @mouseleave="scheduleDropdownClose">
                                    <div class="container mx-auto px-4">
                                        <div
                                            class="bg-white dark:bg-zinc-900 shadow-2xl rounded-2xl border border-gray-200 dark:border-zinc-800 backdrop-blur-xl max-h-[500px] overflow-y-auto mt-0">
                                            <div v-for="node in flattenCategoryTree(item.category)"
                                                :key="`${node.category.id}-${node.depth}`"
                                                class="border-b border-gray-100 dark:border-zinc-800 last:border-0">

                                                <!-- Main Category Section -->
                                                <div class="p-6 hover:bg-gray-50 dark:hover:bg-zinc-800/50 transition-colors"
                                                    :class="{ 'pl-8': node.depth > 0, 'pl-6': node.depth === 0 }">

                                                    <!-- Category Header -->
                                                    <div class="flex items-center justify-between mb-4">
                                                        <Link :href="categoryHref(node.category.slug)"
                                                            class="text-lg font-bold text-gray-900 dark:text-white hover:text-yellow-600 dark:hover:text-yellow-400 transition-colors flex items-center gap-2 group">
                                                        <svg class="w-5 h-5 text-yellow-500" fill="none"
                                                            stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2"
                                                                d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                                        </svg>
                                                        <span>{{ node.category.name }}</span>
                                                        <span v-if="node.category.products_count"
                                                            class="text-xs font-normal text-gray-500 dark:text-gray-400">
                                                            ({{ node.category.products_count }} items)
                                                        </span>
                                                        </Link>
                                                        <Link :href="categoryHref(node.category.slug)"
                                                            class="text-sm font-medium text-yellow-600 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 flex items-center gap-1 group">
                                                        <span>View All</span>
                                                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                stroke-width="2" d="M9 5l7 7-7 7" />
                                                        </svg>
                                                        </Link>
                                                    </div>

                                                    <!-- Featured Products -->
                                                    <div v-if="node.category.products?.length"
                                                        class="grid grid-cols-4 gap-3">
                                                        <Link v-for="product in node.category.products.slice(0, 4)"
                                                            :key="product.id" :href="`/products/${product.slug}`"
                                                            class="group/item border border-gray-200 dark:border-zinc-700 rounded-lg p-3 hover:border-yellow-400 dark:hover:border-yellow-500 hover:shadow-lg transition-all duration-200 bg-white dark:bg-zinc-800/30">
                                                        <div v-if="product.thumbnail_url"
                                                            class="aspect-square mb-2 rounded-lg overflow-hidden bg-gray-100 dark:bg-zinc-800">
                                                            <img :src="product.thumbnail_url" :alt="product.name"
                                                                class="w-full h-full object-cover group-hover/item:scale-110 transition-transform duration-300" />
                                                        </div>
                                                        <div v-else
                                                            class="aspect-square mb-2 rounded-lg bg-gray-100 dark:bg-zinc-800 flex items-center justify-center">
                                                            <svg class="w-8 h-8 text-gray-400" fill="none"
                                                                stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    stroke-width="2"
                                                                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                            </svg>
                                                        </div>
                                                        <h4
                                                            class="text-sm font-medium text-gray-900 dark:text-gray-100 line-clamp-2 mb-1 group-hover/item:text-yellow-600 dark:group-hover/item:text-yellow-400 transition-colors">
                                                            {{ product.name }}</h4>
                                                        <p class="text-sm font-bold text-gray-900 dark:text-white">{{
                                                            formatCurrency(product.price) }}</p>
                                                        </Link>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </transition>
                        </div>

                        <!-- Separator (not after last item) -->
                        <div v-if="index < navigation.length - 1"
                            class="h-6 w-px bg-gradient-to-b from-transparent via-gray-300 dark:via-gray-600 to-transparent">
                        </div>
                    </template>
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
                    <div v-if="showMobileMenu"
                        class="w-80 max-w-[85vw] h-full bg-white dark:bg-zinc-900 shadow-2xl overflow-y-auto"
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
                            <div class="mb-6 pb-6 border-b border-gray-100 dark:border-zinc-800">
                                <template v-if="user">
                                    <!-- User Info Card -->
                                    <div
                                        class="bg-linear-to-br from-yellow-50 to-yellow-100 rounded-xl p-4 mb-4 border border-yellow-200 shadow-sm">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="w-12 h-12 rounded-full border-2 border-yellow-400/50 overflow-hidden ring-2 ring-yellow-400/30 shadow-md shadow-yellow-400/20 shrink-0">
                                                <img :src="user.avatar" :alt="user.name"
                                                    class="w-full h-full object-cover" />
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
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                                    </svg>
                                    <span class="font-medium">Dashboard</span>
                                    </Link>
                                    <Link href="/profile"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">My Profile</span>
                                    </Link>
                                    <Link href="/orders"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    <span class="font-medium">My Orders</span>
                                    </Link>
                                    <Link href="/settings"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-600 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group mb-2">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="font-medium">Settings</span>
                                    </Link>
                                    <div class="border-t border-gray-200 dark:border-zinc-700 my-2"></div>
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
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-500 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group">
                                    <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span class="font-medium">Register</span>
                                    </Link>
                                    <Link href="/login"
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-500 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group">
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
                                        class="flex items-center gap-3 py-3 px-4 text-gray-700 dark:text-gray-200 hover:text-yellow-500 dark:hover:text-yellow-400 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 rounded-xl transition-all duration-300 group">
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
                            <div class="space-y-3">
                                <div v-for="item in navigation" :key="item.name">
                                    <Link :href="item.href"
                                        class="flex items-center justify-between py-3.5 px-4 rounded-xl transition-all duration-300 font-medium group"
                                        :class="isNavItemActive(item) ? 'bg-yellow-100 dark:bg-yellow-500/20 text-yellow-700 dark:text-yellow-400 shadow-inner' : 'text-gray-700 dark:text-gray-200 hover:bg-yellow-50 dark:hover:bg-yellow-500/10 hover:text-yellow-600 dark:hover:text-yellow-400'">
                                    <span>{{ item.name }}</span>
                                    <svg v-if="item.hasDropdown"
                                        class="w-5 h-5 transition-transform group-hover:translate-x-1"
                                        :class="isNavItemActive(item) ? 'text-yellow-500 rotate-90' : 'text-gray-400'"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                    </Link>
                                    <div v-if="item.category?.children?.length"
                                        class="mt-2 flex flex-wrap gap-2 pl-4 border-l border-yellow-100">
                                        <Link v-for="child in item.category.children" :key="child.id"
                                            :href="categoryHref(child.slug)"
                                            class="px-2 py-1 rounded-full bg-gray-100 text-gray-600 text-xs hover:bg-yellow-100 hover:text-yellow-700 transition">
                                        {{ child.name }}
                                        </Link>
                                    </div>
                                </div>
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
