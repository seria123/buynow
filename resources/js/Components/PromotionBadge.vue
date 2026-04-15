<template>
    <div v-if="promotion" class="promotion-badge">
        <!-- Percentage/Fixed Discount Badge -->
        <span v-if="isPercentageOrFixed" 
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold"
            :class="badgeClasses">
            {{ discountText }}
        </span>

        <!-- Flash Sale Badge -->
        <span v-else-if="promotion.is_flash_sale" 
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-red-500 text-white animate-pulse">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            Flash Sale
        </span>

        <!-- Buy One Get One Badge -->
        <span v-else-if="promotion.promotion_type === 'buy_one_get_one'" 
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-purple-500 text-white">
            BOGO
        </span>

        <!-- Free Shipping Badge -->
        <span v-else-if="promotion.promotion_type === 'free_shipping'" 
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-500 text-white">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
            </svg>
            Free Shipping
        </span>

        <!-- Bundle Badge -->
        <span v-else-if="promotion.promotion_type === 'bundle'" 
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-500 text-white">
            Bundle Deal
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    promotion: {
        type: Object,
        default: null
    },
    discountPercentage: {
        type: Number,
        default: 0
    }
});

const isPercentageOrFixed = computed(() => {
    return props.promotion && 
        (props.promotion.promotion_type === 'percentage' || 
         props.promotion.promotion_type === 'fixed');
});

const discountText = computed(() => {
    if (!props.promotion) return '';
    
    if (props.promotion.promotion_type === 'percentage') {
        return `${props.discountPercentage || props.promotion.value}% OFF`;
    }
    
    if (props.promotion.promotion_type === 'fixed') {
        return `KSh ${props.promotion.value} OFF`;
    }
    
    return '';
});

const badgeClasses = computed(() => {
    if (!props.promotion) return '';
    
    if (props.promotion.is_flash_sale) {
        return 'bg-red-500 text-white';
    }
    
    return 'bg-yellow-500 text-white';
});
</script>

<style scoped>
.promotion-badge {
    display: inline-block;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.7;
    }
}

.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
