<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="agent" class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex items-center space-x-6 mb-8">
        <img
          :src="agent.user.profile_picture || defaultAvatar"
          alt="Agent Profile"
          class="w-32 h-32 rounded-full object-cover border-4 border-blue-200"
        />
        <div>
          <h1 class="text-4xl font-bold text-gray-800">{{ agent.user.name }}</h1>
          <p class="text-xl text-gray-600">{{ agent.agency_name }}</p>
          <p class="text-blue-600 mt-2">
            <a :href="`mailto:${agent.user.email}`" class="hover:underline">{{
              agent.user.email
            }}</a>
            <span v-if="agent.user.phone_number">
              |
              <a :href="`tel:${agent.user.phone_number}`" class="hover:underline">{{
                agent.user.phone_number
              }}</a></span
            >
          </p>
          <p v-if="agent.website" class="text-blue-600">
            <a
              :href="agent.website"
              target="_blank"
              rel="noopener noreferrer"
              class="hover:underline"
              >{{ agent.website }}</a
            >
          </p>
        </div>
      </div>

      <div class="mb-8">
        <h2 class="text-2xl font-semibold text-gray-800 mb-3">About Me</h2>
        <p class="text-gray-700 leading-relaxed">
          {{ agent.bio || "No biography provided yet." }}
        </p>
      </div>

      <section>
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">My Listings</h2>
        <div
          v-if="agentListings.length"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
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
    <div v-else class="text-center text-gray-600 py-20">Loading agent profile...</div>
  </div>
</template>

<script setup>
import { ref } from "vue";
import { useRoute, useNuxtApp } from "#app";
import PropertyCard from "~/components/PropertyCard.vue"; // Will be created in Step 7
import defaultAvatar from "~/assets/images/default-avatar.jpg";

const config = useRuntimeConfig();
const { $api } = useNuxtApp();
const route = useRoute();

// Use useAsyncData for SSR support and better data fetching lifecycle
const { data: agentData, error: apiError } = await useAsyncData(
  `agent-${route.params.id}`,
  async () => {
    try {
      const response = await $fetch(
        config.public.apiBaseUrl + `/agents/${route.params.id}`
      );
      return response.data;
    } catch (err) {
      console.error("❌ API call failed:", err.message);
      if (err.response) {
        console.error("Data:", err.response.data);
        console.error("Status:", err.response.status);
      }
      throw err;
    }
  },
  {
    server: true,
  }
);

if (apiError.value) {
  console.error("📉 AsyncData Error:", apiError.value);
}

const agent = computed(() => {
  const data = agentData.value?.data || agentData.value;
  return data;
});

const agentListings = computed(() => agent.value?.properties || []);

// useHead will automatically update when 'agent' (computed) changes
useHead(() => {
  const agentName = agent.value?.user?.name || "Agent";
  const agencyName = agent.value?.agency_name || agent.value?.title || "Agent";

  return {
    title: `${agentName}'s Profile - Real Estate Agent`,
    meta: [
      {
        name: "description",
        content: `View ${agentName}'s profile and listings from ${agencyName}.`,
      },
      {
        property: "og:title",
        content: `${agentName}'s Profile - Real Estate Agent`,
      },
    ],
  };
});
</script>
