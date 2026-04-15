<template>
    <div v-if="isActive && timeRemaining" class="flash-sale-countdown">
        <div class="flex items-center gap-2 bg-red-500 text-white px-3 py-2 rounded-lg">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span class="text-sm font-medium">Ends in:</span>
            <div class="flex items-center gap-1">
                <span class="bg-white/20 px-2 py-1 rounded text-lg font-bold">{{ hours }}</span>
                <span>:</span>
                <span class="bg-white/20 px-2 py-1 rounded text-lg font-bold">{{ minutes }}</span>
                <span>:</span>
                <span class="bg-white/20 px-2 py-1 rounded text-lg font-bold">{{ seconds }}</span>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    expiresAt: {
        type: String,
        required: true
    }
});

const timeRemaining = ref(null);
let intervalId = null;

const calculateTimeRemaining = () => {
    const now = new Date().getTime();
    const expiry = new Date(props.expiresAt).getTime();
    const difference = expiry - now;

    if (difference <= 0) {
        timeRemaining.value = null;
        return;
    }

    timeRemaining.value = {
        hours: Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60)),
        minutes: Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60)),
        seconds: Math.floor((difference % (1000 * 60)) / 1000)
    };
};

const hours = computed(() => {
    if (!timeRemaining.value) return '00';
    return String(timeRemaining.value.hours).padStart(2, '0');
});

const minutes = computed(() => {
    if (!timeRemaining.value) return '00';
    return String(timeRemaining.value.minutes).padStart(2, '0');
});

const seconds = computed(() => {
    if (!timeRemaining.value) return '00';
    return String(timeRemaining.value.seconds).padStart(2, '0');
});

const isActive = computed(() => {
    return timeRemaining.value !== null;
});

onMounted(() => {
    calculateTimeRemaining();
    intervalId = setInterval(calculateTimeRemaining, 1000);
});

onUnmounted(() => {
    if (intervalId) {
        clearInterval(intervalId);
    }
});
</script>

<style scoped>
.flash-sale-countdown {
    display: inline-block;
}
</style>
