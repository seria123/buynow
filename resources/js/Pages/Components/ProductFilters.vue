<script setup>
import { reactive, watch, computed } from 'vue';

const props = defineProps({
    categories: {
        type: Array,
        default: () => [],
    },
    brands: {
        type: Array,
        default: () => [],
    },
    attributes: {
        type: Array,
        default: () => [],
    },
    priceRange: {
        type: Object,
        default: () => ({ min: 0, max: 0 }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['apply', 'clear']);

const numberFormatter = new Intl.NumberFormat('en-KE');

const flattenCategoryList = (categories = [], depth = 0) => {
    return categories.flatMap((category) => [
        { category, depth },
        ...flattenCategoryList(category.children ?? [], depth + 1),
    ]);
};

const findCategoryBySlug = (categories = [], slug) => {
    for (const category of categories) {
        if (category.slug === slug) {
            return category;
        }

        const match = findCategoryBySlug(category.children ?? [], slug);
        if (match) {
            return match;
        }
    }

    return null;
};

const buildLocalFilters = (source) => {
    const attributes = {};
    Object.entries(source?.attributes ?? {}).forEach(([key, values]) => {
        attributes[key] = [...values];
    });

    // Handle both single category (legacy) and array of categories
    let categories = [];
    if (Array.isArray(source?.categories)) {
        categories = [...source.categories];
    } else if (source?.categories) {
        categories = [source.categories];
    } else if (source?.category) {
        // Backward compatibility: single category string
        categories = [source.category];
    }

    return {
        categories,
        brands: [...(source?.brands ?? [])],
        price: {
            min: source?.price?.min ?? props.priceRange?.min ?? 0,
            max: source?.price?.max ?? props.priceRange?.max ?? 0,
        },
        attributes,
    };
};

const localFilters = reactive(buildLocalFilters(props.filters));
const flattenedCategories = computed(() => flattenCategoryList(props.categories ?? []));

// For backward compatibility, get the first selected category for the "Explore" section
const selectedCategoryNode = computed(() => {
    if (!localFilters.categories || localFilters.categories.length === 0) {
        return null;
    }

    return findCategoryBySlug(props.categories ?? [], localFilters.categories[0]);
});
const selectedChildCategories = computed(() => selectedCategoryNode.value?.children ?? []);
const selectedCategoryName = computed(() => selectedCategoryNode.value?.name ?? null);
const selectedCategorySlug = computed(() => selectedCategoryNode.value?.slug ?? null);

watch(
    () => props.filters,
    (next) => {
        const updated = buildLocalFilters(next);
        localFilters.categories = updated.categories;
        localFilters.brands = updated.brands;
        localFilters.price.min = updated.price.min;
        localFilters.price.max = updated.price.max;
        localFilters.attributes = updated.attributes;
    },
    { deep: true }
);

const clampPrice = (key, value) => {
    const min = props.priceRange?.min ?? 0;
    const max = props.priceRange?.max ?? 0;

    let numeric = Number(value);
    if (Number.isNaN(numeric)) {
        numeric = key === 'min' ? min : max;
    }

    if (key === 'min') {
        numeric = Math.max(min, Math.min(numeric, localFilters.price.max));
    } else {
        numeric = Math.min(max, Math.max(numeric, localFilters.price.min));
    }

    if (key === 'min' && numeric > localFilters.price.max) {
        localFilters.price.max = numeric;
    }

    if (key === 'max' && numeric < localFilters.price.min) {
        localFilters.price.min = numeric;
    }

    localFilters.price[key] = numeric;
};

const toggleBrand = (brandId) => {
    const index = localFilters.brands.indexOf(brandId);
    if (index > -1) {
        localFilters.brands.splice(index, 1);
    } else {
        localFilters.brands.push(brandId);
    }
};

const ensureAttributeArray = (attributeId) => {
    if (!localFilters.attributes[attributeId]) {
        localFilters.attributes[attributeId] = [];
    }
};

const toggleAttributeValue = (attributeId, value) => {
    ensureAttributeArray(attributeId);
    const values = localFilters.attributes[attributeId];
    const index = values.indexOf(value);

    if (index > -1) {
        values.splice(index, 1);
        if (values.length === 0) {
            delete localFilters.attributes[attributeId];
        }
    } else {
        values.push(value);
    }
};

const clearAttributeFilter = (attributeId) => {
    if (localFilters.attributes[attributeId]) {
        delete localFilters.attributes[attributeId];
    }
};

const isAttributeValueSelected = (attributeId, value) => {
    return localFilters.attributes[attributeId]?.includes(value) ?? false;
};

const applyFilters = () => {
    emit('apply', {
        categories: [...localFilters.categories],
        brands: [...localFilters.brands],
        price: { ...localFilters.price },
        attributes: { ...localFilters.attributes },
    });
};

const clearFilters = () => {
    localFilters.categories = [];
    localFilters.brands = [];
    localFilters.price.min = props.priceRange?.min ?? 0;
    localFilters.price.max = props.priceRange?.max ?? 0;
    localFilters.attributes = {};

    emit('clear');
};

const formatAmount = (value) => {
    if (value === undefined || value === null) {
        return '0';
    }

    return numberFormatter.format(value);
};

const toggleCategoryFilter = (slug) => {
    if (!slug) {
        // "All categories" checkbox - clear all selected categories
        localFilters.categories = [];
        return;
    }

    const index = localFilters.categories.indexOf(slug);
    if (index > -1) {
        localFilters.categories.splice(index, 1);
    } else {
        localFilters.categories.push(slug);
    }
};

const setCategoryFilter = (slug, autoApply = false) => {
    toggleCategoryFilter(slug);

    if (autoApply) {
        applyFilters();
    }
};

const isCategorySelected = (slug) => {
    if (!slug) {
        return localFilters.categories.length === 0;
    }

    return localFilters.categories.includes(slug);
};

const previewProducts = (category, limit = 3) => {
    return (category.products ?? []).slice(0, limit);
};
</script>

<template>
    <div
        class="bg-white dark:bg-zinc-900 rounded-2xl border border-gray-100 dark:border-zinc-800 shadow-lg dark:shadow-zinc-950/50 px-5 py-6 space-y-6 sticky top-28 max-h-[80vh] overflow-auto backdrop-blur-xl bg-opacity-95 dark:bg-opacity-95">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                Filters
            </h2>
            <button type="button"
                class="text-xs text-yellow-400 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 font-semibold transition-colors"
                @click="clearFilters">
                Clear all
            </button>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-zinc-700">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path
                        d="M2 6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v6a2 2 0 01-2 2H4a2 2 0 01-2-2V6z" />
                </svg>
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Categories
                </h3>
            </div>
            <div class="space-y-2.5 pl-1">
                <label
                    class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300 hover:text-yellow-400 dark:hover:text-yellow-400 transition-colors group">
                    <input type="checkbox"
                        class="rounded border-gray-300 dark:border-zinc-600 text-yellow-400 focus:ring-yellow-500 dark:bg-zinc-800 shadow-sm"
                        :checked="localFilters.categories.length === 0"
                        @change="() => { localFilters.categories = []; }" />
                    <span class="font-medium group-hover:translate-x-0.5 transition-transform">All categories</span>
                </label>
                <label v-for="item in flattenedCategories" :key="item.category.id"
                    class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300 hover:text-yellow-400 dark:hover:text-yellow-400 transition-colors group"
                    :style="{ marginLeft: `${item.depth * 16}px` }">
                    <input type="checkbox"
                        class="rounded border-gray-300 dark:border-zinc-600 text-yellow-400 focus:ring-yellow-500 dark:bg-zinc-800 shadow-sm"
                        :checked="isCategorySelected(item.category.slug)"
                        @change="toggleCategoryFilter(item.category.slug)" />
                    <span class="flex items-center gap-2 group-hover:translate-x-0.5 transition-transform">
                        <span v-if="item.depth > 0"
                            class="inline-flex w-1.5 h-1.5 rounded-full bg-gray-400 dark:bg-gray-600"></span>
                        <span :class="{ 'font-semibold': item.depth === 0 }">{{ item.category.name }}</span>
                    </span>
                </label>
            </div>
        </div>

        <div v-if="selectedChildCategories.length"
            class="space-y-3 rounded-xl border border-yellow-200 dark:border-yellow-900/50 bg-gradient-to-br from-yellow-50 to-yellow-100/50 dark:from-yellow-900/20 dark:to-yellow-800/10 p-4 shadow-inner">
            <div class="flex items-center justify-between">
                <h3 class="text-xs font-bold text-yellow-900 dark:text-yellow-400 uppercase tracking-wider">
                    Explore {{ selectedCategoryName }}
                </h3>
                <button type="button"
                    class="text-xs text-yellow-700 dark:text-yellow-400 hover:text-yellow-900 dark:hover:text-yellow-300 font-semibold transition-colors"
                    @click="setCategoryFilter(selectedCategorySlug)">
                    Focus
                </button>
            </div>

            <div class="space-y-3">
                <div v-for="child in selectedChildCategories" :key="child.id"
                    class="rounded-lg border border-yellow-200/50 dark:border-yellow-800/30 bg-white/90 dark:bg-zinc-800/50 p-3 shadow-sm hover:shadow-md transition-all backdrop-blur-sm">
                    <div class="flex items-start justify-between gap-2">
                        <div>
                            <p class="text-sm font-bold text-gray-900 dark:text-white">
                                {{ child.name }}
                            </p>
                            <p class="text-xs text-gray-500 dark:text-gray-400" v-if="child.children?.length">
                                {{ child.children.length }} subcategories
                            </p>
                        </div>
                        <button type="button"
                            class="text-xs font-semibold text-yellow-400 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 focus:outline-none transition-colors"
                            @click="setCategoryFilter(child.slug, true)">
                            View →
                        </button>
                    </div>

                    <div v-if="child.children?.length" class="mt-3 flex flex-wrap gap-2">
                        <button v-for="grandchild in child.children" :key="grandchild.id" type="button"
                            class="px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 hover:bg-yellow-200 dark:hover:bg-yellow-900/50 transition-colors shadow-sm"
                            @click="setCategoryFilter(grandchild.slug, true)">
                            {{ grandchild.name }}
                        </button>
                    </div>

                    <div v-if="child.products?.length" class="mt-4 grid grid-cols-1 gap-2">
                        <div v-for="product in previewProducts(child)" :key="product.id"
                            class="flex items-center gap-3 rounded-lg border border-gray-200 dark:border-zinc-700 bg-gray-50 dark:bg-zinc-800/80 p-2 hover:shadow-md transition-shadow">
                            <div
                                class="w-12 h-12 rounded-lg bg-white dark:bg-zinc-900 overflow-hidden flex items-center justify-center shadow-sm">
                                <img v-if="product.thumbnail_url" :src="product.thumbnail_url" :alt="product.name"
                                    class="w-full h-full object-cover" />
                                <div v-else class="text-gray-400 dark:text-gray-600 text-xs font-medium">
                                    No Image
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ product.name
                                    }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ product.variants?.length ?? 0 }}
                                    variant(s)</p>
                                <div class="text-xs font-bold text-yellow-400 dark:text-yellow-400">
                                    {{ formatAmount(product.price) }} KES
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-zinc-700">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z"
                        clip-rule="evenodd" />
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                        clip-rule="evenodd" />
                </svg>
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Price Range
                </h3>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div class="space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 font-semibold">Min</label>
                    <input type="number"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-700 focus:border-yellow-500 focus:ring-yellow-500 text-sm bg-gray-50 dark:bg-zinc-800 dark:text-white shadow-sm"
                        :min="priceRange?.min" :max="priceRange?.max" :value="localFilters.price.min"
                        @change="clampPrice('min', $event.target.value)" />
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] uppercase tracking-wide text-gray-500 dark:text-gray-400 font-semibold">Max</label>
                    <input type="number"
                        class="w-full rounded-lg border-gray-300 dark:border-zinc-700 focus:border-yellow-500 focus:ring-yellow-500 text-sm bg-gray-50 dark:bg-zinc-800 dark:text-white shadow-sm"
                        :min="priceRange?.min" :max="priceRange?.max" :value="localFilters.price.max"
                        @change="clampPrice('max', $event.target.value)" />
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">
                Range: KES {{ formatAmount(priceRange?.min) }} – KES {{ formatAmount(priceRange?.max) }}
            </p>
        </div>

        <div v-if="brands.length" class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-zinc-700">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
                        clip-rule="evenodd" />
                </svg>
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Brands
                </h3>
            </div>
            <div class="space-y-2.5 pl-1">
                <label v-for="brand in brands" :key="brand.id"
                    class="flex items-center gap-3 cursor-pointer text-sm text-gray-700 dark:text-gray-300 hover:text-yellow-400 dark:hover:text-yellow-400 transition-colors group">
                    <input type="checkbox"
                        class="rounded border-gray-300 dark:border-zinc-600 text-yellow-400 focus:ring-yellow-500 dark:bg-zinc-800 shadow-sm"
                        :checked="localFilters.brands.includes(brand.id)" @change="toggleBrand(brand.id)" />
                    <span class="font-medium group-hover:translate-x-0.5 transition-transform">{{ brand.name }}</span>
                </label>
            </div>
        </div>

        <div class="space-y-4">
            <div class="flex items-center gap-2 pb-2 border-b border-gray-200 dark:border-zinc-700">
                <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z"
                        clip-rule="evenodd" />
                </svg>
                <h3 class="text-xs font-bold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                    Attributes
                </h3>
            </div>
            <div v-if="attributes.length" class="space-y-5">
                <div v-for="attribute in attributes" :key="attribute.id"
                    class="border-b border-gray-200 dark:border-zinc-800 last:border-b-0 pb-5 last:pb-0">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white">{{ attribute.name }}</h3>
                        <button v-if="localFilters.attributes[attribute.id]?.length"
                            @click="clearAttributeFilter(attribute.id)"
                            class="text-xs text-yellow-400 dark:text-yellow-400 hover:text-yellow-700 dark:hover:text-yellow-300 font-semibold transition-colors">
                            Clear
                        </button>
                    </div>
                    <div class="space-y-2.5 pl-1">
                        <label v-for="value in attribute.values" :key="value"
                            class="flex items-center cursor-pointer group">
                            <input type="checkbox" :checked="isAttributeValueSelected(attribute.id, value)"
                                @change="toggleAttributeValue(attribute.id, value)"
                                class="h-4 w-4 text-yellow-400 focus:ring-yellow-500 border-gray-300 dark:border-zinc-600 rounded cursor-pointer dark:bg-zinc-800 shadow-sm" />
                            <span
                                class="ml-3 text-sm text-gray-700 dark:text-gray-300 group-hover:text-yellow-400 dark:group-hover:text-yellow-400 transition-all group-hover:translate-x-0.5"
                                :class="{ 'font-bold text-yellow-700 dark:text-yellow-400': isAttributeValueSelected(attribute.id, value) }">
                                {{ value }}
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <div v-else
                class="text-sm text-gray-500 dark:text-gray-400 text-center py-6 bg-gray-50 dark:bg-zinc-800/50 rounded-lg">
                No filters available
            </div>
        </div>

        <button type="button"
            class="w-full inline-flex justify-center items-center gap-2 rounded-xl bg-gradient-to-r from-yellow-500 to-yellow-400 hover:from-yellow-400 hover:to-yellow-700 text-white font-bold py-3 shadow-lg shadow-yellow-500/30 hover:shadow-yellow-400/40 transition-all hover:scale-[1.02] active:scale-[0.98]"
            @click="applyFilters">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            Apply Filters
        </button>
    </div>
</template>
