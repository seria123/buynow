<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';

const page = usePage();
const user = page.props.user || {};
</script>

<template>
    <MainLayout>
  <ProfileLayout>
    <div class="p-4">
      <!-- Avatar -->
      <div class="flex items-center gap-4 mb-6">
        <img 
          :src="user.avatar" 
          alt="Profile Avatar" 
          class="w-20 h-20 rounded-full object-cover border-2 border-yellow-400 shadow-lg"
        />
        <div>
          <h1 class="text-2xl font-bold">Welcome, {{ user.name }}</h1>
          <p class="text-gray-500 text-sm">@{{ user.username }}</p>
        </div>
      </div>

      <div class="mt-4 space-y-2">
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Phone:</strong> {{ user.phone ?? 'Not provided' }}</p>
      </div>

      <Link href="/profile/edit" class="mt-6 inline-block bg-blue-500 text-white px-4 py-2 rounded">
        Edit Profile
      </Link>
    </div>
  </ProfileLayout>
  </MainLayout>
</template>