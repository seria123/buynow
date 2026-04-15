<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    ads: {
        type: Array,
        default: () => [],
    },
    autoplayInterval: {
        type: Number,
        default: 5000,
    },
});

const currentIndex = ref(0);
let autoplayTimer = null;

const nextSlide = () => {
    currentIndex.value = (currentIndex.value + 1) % props.ads.length;
};

const prevSlide = () => {
    currentIndex.value = (currentIndex.value - 1 + props.ads.length) % props.ads.length;
};

const goToSlide = (index) => {
    currentIndex.value = index;
};

const startAutoplay = () => {
    if (props.ads.length > 1) {
        autoplayTimer = setInterval(nextSlide, props.autoplayInterval);
    }
};

const stopAutoplay = () => {
    if (autoplayTimer) {
        clearInterval(autoplayTimer);
        autoplayTimer = null;
    }
};

onMounted(() => {
    startAutoplay();
});

onUnmounted(() => {
    stopAutoplay();
});
</script>

<template>
    <div 
        v-if="ads.length > 0" 
        class="relative w-full overflow-hidden group"
        @mouseenter="stopAutoplay"
        @mouseleave="startAutoplay"
    >
        <!-- Carousel Container -->
        <div class="relative w-full h-[350px] md:h-[450px] lg:h-[550px] bg-gray-100 dark:bg-zinc-800">
            <!-- Slides -->
            <TransitionGroup name="fade" tag="div" class="w-full h-full flex items-center justify-center">
                <div 
                    v-for="(ad, index) in ads" 
                    :key="ad.id"
                    v-show="index === currentIndex"
                    class="absolute inset-0 w-full h-full"
                >
                    <a 
                        :href="ad.link || '/products'"
                        class="block w-full h-full"
                        :target="ad.link && !ad.link.startsWith('/') ? '_blank' : '_self'"
                    >
                        <!-- Image -->
                        <img 
                            :src="ad.image" 
                            :alt="ad.title"
                            class="w-full h-full object-cover object-center"
                        />
                        
                        <!-- Overlay Content -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent flex items-end">
                            <div class="p-2 md:p-3 w-full">
                                <h3 v-if="ad.title" class="text-white text-lg md:text-2xl font-bold mb-1">
                                    {{ ad.title }}
                                </h3>
                                <p v-if="ad.subtitle" class="text-white/90 text-sm md:text-base">
                                    {{ ad.subtitle }}
                                </p>
                            </div>
                        </div>
                    </a>
                </div>
            </TransitionGroup>
        </div>

        <!-- Navigation Arrows -->
        <button 
            v-if="ads.length > 1"
            @click="prevSlide"
            class="absolute left-2 md:left-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-colors z-10"
        >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </button>
        
        <button 
            v-if="ads.length > 1"
            @click="nextSlide"
            class="absolute right-2 md:right-4 top-1/2 -translate-y-1/2 w-10 h-10 bg-white/80 hover:bg-white rounded-full flex items-center justify-center shadow-lg transition-colors z-10"
        >
            <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
        </button>

        <!-- Dots Navigation -->
        <div v-if="ads.length > 1" class="absolute bottom-3 md:bottom-4 left-1/2 -translate-x-1/2 flex gap-2 z-10">
            <button 
                v-for="(ad, index) in ads" 
                :key="ad.id"
                @click="goToSlide(index)"
                class="w-2 h-2 rounded-full transition-colors"
                :class="index === currentIndex ? 'bg-white' : 'bg-white/50'"
            />
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>