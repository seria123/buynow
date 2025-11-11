<script setup>
</script>

<template>
    <div
        class="min-h-screen bg-linear-to-br from-slate-50 to-slate-100 dark:from-[#0a0a0a] dark:to-zinc-950 flex items-center justify-center p-4 transition-colors duration-300">
        <!-- Main Container -->
        <div class="w-full max-w-6xl">
            <div class="bg-white dark:bg-zinc-900 rounded-3xl shadow-2xl overflow-hidden" style="height: 850px;">
                <div class="grid md:grid-cols-2 h-full">
                    <!-- Left Side - Branding & Info -->
                    <div
                        class="bg-linear-to-br from-zinc-900 via-zinc-800 to-zinc-950 dark:from-zinc-900 dark:via-zinc-800 dark:to-zinc-950 p-12 flex flex-col justify-center relative overflow-hidden border-r border-yellow-400/10 shadow-xl h-full">
                        <!-- Animated Decorative Elements -->
                        <div
                            class="absolute top-0 right-0 w-96 h-96 bg-yellow-400 opacity-20 rounded-full blur-3xl animate-pulse">
                        </div>
                        <div class="absolute bottom-0 left-0 w-72 h-72 bg-yellow-300 opacity-10 rounded-full blur-3xl animate-pulse"
                            style="animation-delay: 1s;"></div>
                        <div class="absolute top-1/3 right-1/4 w-48 h-48 bg-yellow-500 opacity-5 rounded-full blur-2xl">
                        </div>

                        <div class="relative z-10">
                            <!-- Logo -->
                            <div class="flex items-center mb-8">
                                <div
                                    class="w-14 h-14 bg-yellow-400 rounded-2xl shadow-lg shadow-yellow-400/50 flex items-center justify-center mr-4">
                                    <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 24 24">
                                        <path
                                            d="M13 7.5h5v2h-5m-2 4.5h7v2h-7m3-9h4v2h-4M6 11.5h5v2H6m-2 4.5h7v2H4m7-9h3v2h-3m-7 0h5v2H4z" />
                                    </svg>
                                </div>
                                <span class="text-4xl font-bold text-yellow-400">Buynow</span>
                            </div>

                            <!-- Welcome Text -->
                            <h2 class="text-3xl font-bold text-white mb-4">
                                Welcome to Buynow - Kenya's Largest Electronics Store
                            </h2>
                            <p class="text-zinc-200 text-lg mb-8 leading-relaxed">
                                Shop for what you love. Timepieces, gadgets, and more. All prices in KES.
                            </p>

                            <!-- Features -->
                            <div class="space-y-4">
                                <div
                                    class="flex items-center text-yellow-100 bg-yellow-400/10 dark:bg-zinc-800/80 backdrop-blur-sm rounded-xl p-4 border border-yellow-400/30 hover:bg-yellow-400/20 hover:border-yellow-400/50 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 bg-yellow-400/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-yellow-400/30 transition-colors">
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">Free Delivery from KES 5000</span>
                                </div>
                                <div
                                    class="flex items-center text-yellow-100 bg-yellow-400/10 dark:bg-zinc-800/80 backdrop-blur-sm rounded-xl p-4 border border-yellow-400/30 hover:bg-yellow-400/20 hover:border-yellow-400/50 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 bg-yellow-400/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-yellow-400/30 transition-colors">
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">99% Customer Feedbacks</span>
                                </div>
                                <div
                                    class="flex items-center text-yellow-100 bg-yellow-400/10 dark:bg-zinc-800/80 backdrop-blur-sm rounded-xl p-4 border border-yellow-400/30 hover:bg-yellow-400/20 hover:border-yellow-400/50 transition-all duration-300 group">
                                    <div
                                        class="w-10 h-10 bg-yellow-400/20 rounded-lg flex items-center justify-center mr-4 group-hover:bg-yellow-400/30 transition-colors">
                                        <svg class="w-5 h-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                    <span class="font-medium">365 Days for free return</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Side - Auth Form Slot -->
                    <div class="p-12 flex flex-col items-center justify-center bg-white dark:bg-zinc-900 transition-colors duration-300 h-full"
                        style="max-height: 850px; overflow-y: auto;">
                        <div class="w-full max-w-md flex flex-col justify-center py-10">
                            <slot></slot>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
@keyframes pulse {

    0%,
    100% {
        opacity: 0.2;
        transform: scale(1);
    }

    50% {
        opacity: 0.15;
        transform: scale(1.05);
    }
}

.animate-pulse {
    animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
