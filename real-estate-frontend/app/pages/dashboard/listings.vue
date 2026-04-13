<template>
  <div class="container mx-auto px-4 py-8">
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">My Listings</h2>
    <div
      v-if="myListings.length"
      class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6"
    >
      <PropertyCard
        v-for="property in myListings"
        :key="property.id"
        :property="property"
        :show-actions="false"
      />
    </div>
    <div v-else class="text-center text-gray-600 py-10">
      You haven't listed any properties yet.
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useNuxtApp } from "#app";
import PropertyCard from "~/components/PropertyCard.vue";
import { useAuthStore } from "~/stores/auth";

const api = useApi();
const authStore = useAuthStore();

const myListings = ref([]);

const fetchMyListings = async () => {
  try {
    // Assuming an API endpoint to get properties owned by the authenticated user
    const response = await api.getProperties({
      user_id: authStore.user.id,
    });
    myListings.value = response.data.data;
  } catch (error) {
    console.error("Error fetching my listings:", error);
  }
};

onMounted(fetchMyListings);
</script>
