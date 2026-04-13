<template>
  <div class="bg-gray-50">
    <!-- Page Header -->
    <div class="py-16 bg-white shadow-sm">
      <div class="container mx-auto px-4 text-center">
        <h1 class="text-4xl font-bold text-gray-800">Meet Our Agents</h1>
        <p class="text-lg text-gray-600 mt-2">
          The best in the business, ready to help you find your dream home.
        </p>
      </div>
    </div>
    <div class="container mx-auto px-4 py-12">
      <!-- Agents Grid -->
      <div
        v-if="isLoading"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
      >
        <AgentCardSkeleton v-for="n in perPage" :key="n" />
      </div>
      <div
        v-else-if="agents.length > 0"
        class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8"
      >
        <AgentCard v-for="agent in agents" :key="agent.id" :agent="agent" />
      </div>

      <div v-else class="text-center py-16">
        <h2 class="text-2xl font-semibold text-gray-700">No Agents Found</h2>
        <p class="text-gray-500 mt-2">Please check back later.</p>
      </div>

      <!-- Pagination -->
      <div v-if="!isLoading && meta && meta.last_page > 1" class="mt-12">
        <Pagination
          :lastPage="meta.last_page"
          :page="meta.current_page"
          @page-change="handlePageChange"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from "vue";
import { useRoute, useRouter, useNuxtApp } from "#app";

const { $api } = useNuxtApp();
const route = useRoute();

const agents = ref([]);
const meta = ref(null);
const isLoading = ref(true);
const perPage = 12;

const fetchAgents = async (page = 1) => {
  isLoading.value = true;
  try {
    const response = await $api.get("/agents", {
      params: {
        page: page,
        per_page: perPage,
      },
    });
    agents.value = response.data.data;
    meta.value = response.data.meta;
  } catch (error) {
    console.error("Failed to fetch agents:", error);
    // Handle error (e.g., show a notification)
  } finally {
    isLoading.value = false;
  }
};

const handlePageChange = (newPage) => {
  const router = useRouter();
  router.push({ query: { page: newPage } });
};

// Fetch agents on initial load
onMounted(() => {
  const initialPage = parseInt(route.query.page) || 1;
  fetchAgents(initialPage);
});

// Watch for query changes (for pagination)
watch(
  () => route.query.page,
  (newPage) => {
    fetchAgents(parseInt(newPage) || 1);
  }
);

useHead({
  title: "Our Agents - Real Estate",
  meta: [
    {
      name: "description",
      content: "Find the best real estate agents to help you with your property needs.",
    },
  ],
});
</script>
