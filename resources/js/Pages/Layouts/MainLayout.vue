<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Nav from '../Components/Nav.vue';
import { useDarkMode } from '../../composables/useDarkMode';

const { isDark, toggleDarkMode } = useDarkMode();
</script>

<template>
<Head title="BuyNow" />
   
    <div class="min-h-screen">
       
    <div class="min-h-screen bg-white dark:bg-zinc-950 transition-colors duration-300">
        <Nav :isDark="isDark" @toggle-dark-mode="toggleDarkMode" />
        <slot />
    </div>
    
    </div>
</template>
