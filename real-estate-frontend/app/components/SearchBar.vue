<template>
  <form @submit.prevent="search" class="flex flex-col md:flex-row gap-4 p-4 bg-white rounded-lg shadow-lg max-w-3xl mx-auto">
    <input
      type="text"
      v-model="keyword"
      placeholder="Enter keyword (e.g., 'luxury villa', 'beachfront')"
      class="flex-grow p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
    />
    <select
      v-model="cityId"
      class="p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 md:w-1/4 text-black"
    >
      <option value="">All Cities</option>
      <option v-for="city in cities" :key="city.id" :value="city.id">{{ city.name }}</option>
    </select>
    <button type="submit" class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 flex-shrink-0">
      Search
    </button>
  </form>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter, useNuxtApp } from '#app';

const { $api } = useNuxtApp();
const router = useRouter();

const keyword = ref('');
const cityId = ref('');
const cities = ref([]);

// Fetch cities for the dropdown
onMounted(async () => {
  try {
    const response = await $api.get('/cities'); // Assuming an API endpoint for cities
    cities.value = response.data.data;
  } catch (error) {
    console.error('Error fetching cities:', error);
  }
});

const search = () => {
  const query = {};
  if (keyword.value) {
    query.keyword = keyword.value;
  }
  if (cityId.value) {
    query.city_id = cityId.value;
  }
  router.push({ path: '/properties', query });
};
</script>