<template>
  <div>
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Admin Dashboard</h1>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Total Users</h2>
        <p class="text-3xl font-bold text-blue-600">{{ stats.totalUsers }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Total Properties</h2>
        <p class="text-3xl font-bold text-green-600">{{ stats.totalProperties }}</p>
      </div>
      <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-semibold mb-2">Pending Properties</h2>
        <p class="text-3xl font-bold text-yellow-600">{{ stats.pendingProperties }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';

definePageMeta({
  layout: 'admin',
  middleware: ['admin'],
});

const { $api } = useNuxtApp();
const stats = ref({
  totalUsers: 0,
  totalProperties: 0,
  pendingProperties: 0,
});

useHead({
  title: 'Admin Real Estate Listing - Find Your Dream Home',
  meta: [
    { name: 'description', content: 'Search properties for sale and rent. Find houses, apartments, condos, and land listings.' },
    { property: 'og:title', content: 'Real Estate Listing - Find Your Dream Home' },
    { property: 'og:description', content: 'Search properties for sale and rent. Find houses, apartments, condos, and land listings.' },
    { property: 'og:image', content: '~/assets/images/og-home.jpg' },
    { property: 'og:url', content: 'https://yourdomain.com/' },
  ],
});

onMounted(async () => {
  try {
    const response = await $api.get('/admin/dashboard-stats');
    stats.value = response.data;
  } catch (error) {
    console.error('Error fetching dashboard stats:', error);
  }
});
</script>