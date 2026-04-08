<template>
  <div>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">My Listings</h2>
    <NuxtLink to="/dashboard/listings/create" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors duration-200 mb-6">
      Add New Property
    </NuxtLink>
    <div v-if="myListings.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <PropertyCard
        v-for="property in myListings"
        :key="property.id"
        :property="property"
        :show-actions="true"
      />
    </div>
    <div v-else class="text-center text-gray-600 py-10">
      You haven't listed any properties yet.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';
import PropertyCard from '~/components/PropertyCard.vue';

const { $api } = useNuxtApp();
const myListings = ref([]);

const fetchMyListings = async () => {
  try {
    // Assuming an API endpoint to get properties owned by the authenticated user
    const response = await $api.get('/user/properties');
    myListings.value = response.data.data;
  } catch (error) {
    console.error('Error fetching my listings:', error);
  }
};

onMounted(fetchMyListings);
</script>