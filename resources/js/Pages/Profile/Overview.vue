<script setup>
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const user = page.props.user;
</script>
<template>
  <div>
    <h1 class="text-3xl font-bold">Account Overview</h1>
    <p class="text-gray-500 mt-2">Welcome, {{ user.name }}</p>
    <!-- Add more profile overview content here -->
  </div>
</template>