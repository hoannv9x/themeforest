<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="agent" class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex items-center space-x-6 mb-8">
        <img :src="agent.user.profile_picture || defaultAvatar" alt="Agent Profile" class="w-32 h-32 rounded-full object-cover border-4 border-blue-200">
        <div>
          <h1 class="text-4xl font-bold text-gray-800">{{ agent.user.name }}</h1>
          <p class="text-xl text-gray-600">{{ agent.agency_name }}</p>
          <p class="text-blue-600 mt-2">
            <a :href="`mailto:${agent.user.email}`" class="hover:underline">{{ agent.user.email }}</a>
            <span v-if="agent.user.phone_number"> | <a :href="`tel:${agent.user.phone_number}`" class="hover:underline">{{ agent.user.phone_number }}</a></span>
          </p>
          <p v-if="agent.website" class="text-blue-600">
            <a :href="agent.website" target="_blank" rel="noopener noreferrer" class="hover:underline">{{ agent.website }}</a>
          </p>
        </div>
      </div>

      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-3">About Me</h2>
        <p class="text-gray-700 leading-relaxed">{{ agent.bio || 'No biography provided yet.' }}</p>
      </div>

      <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">My Listings</h2>
        <div v-if="agentListings.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
          <PropertyCard
            v-for="property in agentListings"
            :key="property.id"
            :property="property"
          />
        </div>
        <div v-else class="text-center text-gray-600 py-10">
          This agent currently has no active listings.
        </div>
      </section>
    </div>
    <div v-else class="text-center text-gray-600 py-20">
      Loading agent profile...
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRoute, useNuxtApp } from '#app';
import PropertyCard from '~/components/PropertyCard.vue'; // Will be created in Step 7
import defaultAvatar from '~/assets/images/default-avatar.png';

const { $api } = useNuxtApp();
const route = useRoute();
const agent = ref(null);
const agentListings = ref([]);

const fetchAgentData = async () => {
  try {
    // Assuming an API endpoint for fetching agent details and their properties
    const agentResponse = await $api.get(`/agents/${route.params.id}`);
    agent.value = agentResponse.data.data; // Assuming agent data is nested under 'data'

    const listingsResponse = await $api.get(`/agents/${route.params.id}/properties`);
    agentListings.value = listingsResponse.data.data;
  } catch (error) {
    console.error('Error fetching agent data:', error);
    // Handle 404 or other errors
  }
};

onMounted(fetchAgentData);

useHead(() => ({
  title: agent.value ? `${agent.value.user.name}'s Profile - Real Estate Agent` : 'Agent Profile',
  meta: [
    { name: 'description', content: agent.value ? `View ${agent.value.user.name}'s profile and listings from ${agent.value.agency_name}.` : 'Real estate agent profile and listings.' },
  ],
}));
</script>