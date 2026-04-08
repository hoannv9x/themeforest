<template>
  <div>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">My Favorite Properties</h2>
    <div v-if="favorites.length" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <PropertyCard
        v-for="favorite in favorites"
        :key="favorite.id"
        :property="favorite.property"
      />
    </div>
    <div v-else class="text-center text-gray-600 py-10">
      You haven't saved any favorite properties yet.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useNuxtApp } from '#app';
import PropertyCard from '~/components/PropertyCard.vue';

const { $api } = useNuxtApp();
const favorites = ref([]);

const fetchFavorites = async () => {
  try {
    const response = await $api.get('/favorites');
    favorites.value = response.data.data; // Assuming API returns paginated data
  } catch (error) {
    console.error('Error fetching favorites:', error);
  }
};

onMounted(fetchFavorites);
</script>