<template>
    <div class="discounted-price">
        <!-- Original Price (strikethrough) -->
        <span v-if="hasDiscount" class="text-gray-400 line-through text-sm">
            {{ formatCurrency(originalPrice) }}
        </span>
        
        <!-- Discounted Price -->
        <span class="font-bold" :class="priceClasses">
            {{ formatCurrency(discountedPrice) }}
        </span>
        
        <!-- Discount Percentage Badge -->
        <span v-if="showBadge && discountPercentage > 0" 
            class="ml-2 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-bold bg-red-500 text-white">
            -{{ discountPercentage }}%
        </span>
    </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    originalPrice: {
        type: Number,
        required: true
    },
    discountedPrice: {
        type: Number,
        required: true
    },
    discountPercentage: {
        type: Number,
        default: 0
    },
    showBadge: {
        type: Boolean,
        default: true
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
});

const hasDiscount = computed(() => {
    return props.discountedPrice < props.originalPrice;
});

const priceClasses = computed(() => {
    const baseClasses = 'text-gray-900 dark:text-white';
    
    switch (props.size) {
        case 'sm':
            return `${baseClasses} text-sm`;
        case 'lg':
            return `${baseClasses} text-xl`;
        default:
            return `${baseClasses} text-base`;
    }
});

const formatCurrency = (value) => {
    if (value === null || value === undefined) {
        return '—';
    }
    
    return new Intl.NumberFormat('en-KE', {
        style: 'currency',
        currency: 'KES',
        maximumFractionDigits: 2
    }).format(Number(value));
};
</script>

<style scoped>
.discounted-price {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}
</style>
