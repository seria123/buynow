<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import Nav from '../Components/Nav.vue';
import SupportChat from '../Support/Chat.vue';
import { useDarkMode } from '../../composables/useDarkMode';

const { isDark, toggleDarkMode } = useDarkMode();
</script>

<template>
<Head>
  <Title>Buynow - Premium Electronics & Gadgets in Kenya</Title>
  <meta name="description" content="Shop premium electronics, gadgets, smartphones, laptops and more at Buynow Kenya. Best prices, fast delivery, secure payment via M-Pesa." />
  <meta name="keywords" content="electronics, gadgets, Kenya, smartphones, laptops, buy online, M-Pesa payments" />
  <meta property="og:site_name" content="Buynow" />
  <meta property="og:locale" content="en_KE" />
  <meta name="twitter:site" content="@buynow" />
  <link rel="canonical" :href="$page.url" />
</Head>
   
    <div class="min-h-screen">
       
    <div class="min-h-screen bg-white dark:bg-zinc-950 transition-colors duration-300">
        <Nav :isDark="isDark" @toggle-dark-mode="toggleDarkMode" />
        <slot />
    </div>
    
    <!-- Customer Support Chat -->
    <SupportChat />
    
    </div>
</template>
