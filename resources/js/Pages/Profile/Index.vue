<script setup>
import { usePage, Link } from '@inertiajs/vue3';
import ProfileLayout from '../Layouts/ProfileLayout.vue';
import MainLayout from '../Layouts/MainLayout.vue';

const page = usePage();
const user = page.props.user; // Or page.props.auth.user if that's your setup
</script>

<template>
    <MainLayout>
  <ProfileLayout>
    <div class="p-4">
      <h1 class="text-2xl font-bold">Welcome, {{ user.name }}</h1>

      <div class="mt-4">
        <p><strong>Email:</strong> {{ user.email }}</p>
        <p><strong>Username:</strong> {{ user.username }}</p>
        <p><strong>Phone:</strong> {{ user.phone ?? 'Not provided' }}</p>
      </div>

      <Link href="/profile/edit" class="mt-6 inline-block bg-blue-500 text-white px-4 py-2 rounded">
        Edit Profile
      </Link>
    </div>
  </ProfileLayout>
  </MainLayout>
</template>