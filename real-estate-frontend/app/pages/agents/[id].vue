<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="agent" class="bg-white rounded-lg shadow-lg p-6">
      <div class="flex items-center space-x-6 mb-8">
        <img
          :src="agent.user.avatar || defaultAvatar"
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
        <SkeletonLoading
          v-if="isLoading"
          :count="6"
          :columns="3"
          :showImage="true"
          :rows="2"
        />
        <div
          v-else-if="properties.length"
          class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
        >
          <PropertyCard
            v-for="property in properties"
            :key="property.id"
            :property="property"
          />
        </div>
        <div v-else class="text-center text-gray-600 py-10">
          This agent currently has no active listings.
        </div>
      </section>
    </div>
    <div v-if="propertiesMeta && propertiesMeta.last_page > 1" class="mt-12">
      <Pagination
        :lastPage="propertiesMeta.last_page"
        :page="propertiesMeta.current_page"
        @page-change="handlePageChange"
      />
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import { useRoute, useRouter, useAsyncData, createError } from "#app";
import PropertyCard from "~/components/PropertyCard.vue";
import SkeletonLoading from "~/components/common/SkeletonLoading.vue";
import Pagination from "~/components/Pagination.vue";
import defaultAvatar from "~/assets/images/default-avatar.jpg";

const route = useRoute();
const router = useRouter();
const api = useApi();
const config = useRuntimeConfig();

const agentId = route.params.id;
const currentPage = computed(() => parseInt(route.query.page) || 1);
const propertiesMeta = ref([]);
const properties = ref([]);
const isLoading = ref(true);

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

async function getAgentProperties() {
  isLoading.value = true;
  try {
    const { data } = await api.getProperties({
      agent_id: agentId,
      page: currentPage.value,
      per_page: 6,
    });
    properties.value = data.data;
    propertiesMeta.value = data.meta;
  } catch (error) {
    console.error("❌ API call failed:", error.message);
    if (error.response) {
      console.error("Data:", error.response.data);
      console.error("Status:", error.response.status);
    }
    throw createError("❌ Failed to fetch properties:", error.message);
  } finally {
    isLoading.value = false;
  }
}

onMounted(() => {
  getAgentProperties();
});

// Watch for route changes to re-fetch properties (e.g., clicking a related property)
watch(() => route.query.page, getAgentProperties);

// Computed properties to safely access nested data
const agent = computed(() => agentData.value);

// Handle pagination change
const handlePageChange = (newPage) => {
  router.push({ query: { page: newPage } });
};

// SEO Head
useHead(() => ({
  title: agent.value ? `${agent.value.title} - Real Estate Agent` : "Agent Profile",
  meta: [
    {
      name: "description",
      content: agent.value
        ? `View profile and listings for ${
            agent.value.name
          }. ${agent.value.bio?.substring(0, 150)}`
        : "Find the best real estate agents.",
    },
  ],
}));
</script>
