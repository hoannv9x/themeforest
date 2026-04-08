<template>
  <div class="container mx-auto px-4 py-8">
    <h1 class="text-4xl font-bold text-gray-800 mb-8">Properties for Sale</h1>

    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Filter Sidebar -->
      <aside class="lg:w-1/4">
        <FilterSidebar @apply-filters="handleApplyFilters" /> <!-- Reusable FilterSidebar component -->
      </aside>

      <!-- Property Listings -->
      <main class="lg:w-3/4">
        <div class="flex justify-between items-center mb-6">
          <p class="text-gray-600">{{ totalProperties }} results found</p>
          <div class="flex items-center space-x-4">
            <label for="sort" class="text-gray-700">Sort by:</label>
            <select id="sort" v-model="sortBy" @change="fetchProperties" class="border rounded-md p-2">
              <option value="published_at_desc">Newest</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="views_count_desc">Popularity</option>
            </select>
          </div>
        </div>

        <div v-if="properties.length" class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <PropertyCard
            v-for="property in properties"
            :key="property.id"
            :property="property"
          />
        </div>
        <div v-else class="text-center text-gray-600 py-10">
          No properties found matching your criteria.
        </div>

        <!-- Pagination -->
        <div v-if="totalPages > 1" class="mt-10">
          <Pagination
            :currentPage="currentPage"
            :totalPages="totalPages"
            @page-change="handlePageChange"
          />
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { useRoute, useRouter, useNuxtApp } from '#app';
// Import components (will be created in Step 7)
import FilterSidebar from '~/components/FilterSidebar.vue';
import PropertyCard from '~/components/PropertyCard.vue';
import Pagination from '~/components/Pagination.vue';

const { $api } = useNuxtApp();
const route = useRoute();
const router = useRouter();

const properties = ref([]);
const currentPage = ref(parseInt(route.query.page) || 1);
const totalPages = ref(1);
const totalProperties = ref(0);
const sortBy = ref(route.query.sort_by || 'published_at_desc');
const currentFilters = ref({});

// Function to fetch properties from the API
const fetchProperties = async () => {
  try {
    const parts = sortBy.value.split('_');
    const sortDirection = parts.pop();
    const sortColumn = parts.join('_');
    const params = {
      page: currentPage.value,
      sort_by: sortColumn,
      sort_direction: sortDirection,
      ...currentFilters.value,
    };

    // Update URL query parameters
    router.push({ query: { ...route.query, ...params } });

    const response = await $api.get('/properties', { params });
    properties.value = response.data.data;
    currentPage.value = response.data.meta.current_page;
    totalPages.value = response.data.meta.last_page;
    totalProperties.value = response.data.meta.total;
  } catch (error) {
    console.error('Error fetching properties:', error);
  }
};

// Handle filter changes from FilterSidebar
const handleApplyFilters = (filters) => {
  currentFilters.value = filters;
  currentPage.value = 1; // Reset to first page on new filters
  fetchProperties();
};

// Handle page changes from Pagination component
const handlePageChange = (page) => {
  currentPage.value = page;
  fetchProperties();
};

// Initial fetch when component mounts
onMounted(() => {
  // Initialize filters from URL query on mount
  currentFilters.value = {
    city_id: route.query.city_id,
    property_type_id: route.query.property_type_id,
    min_price: route.query.min_price,
    max_price: route.query.max_price,
  };
  fetchProperties();
});

// Watch for changes in route query parameters (e.g., direct URL changes)
watch(route.query, () => {
  currentPage.value = parseInt(route.query.page) || 1;
  sortBy.value = route.query.sort_by || 'published_at_desc';
  currentFilters.value = {
    city_id: route.query.city_id,
    property_type_id: route.query.property_type_id,
    min_price: route.query.min_price,
    max_price: route.query.max_price,
  };
  fetchProperties();
});

// SEO optimization for the property listing page
useHead({
  title: 'Property Listings - Real Estate',
  meta: [
    { name: 'description', content: 'Browse all properties for sale and rent. Filter by price, city, and property type.' },
    { property: 'og:title', content: 'Property Listings - Real Estate' },
    { property: 'og:description', content: 'Browse all properties for sale and rent. Filter by price, city, and property type.' },
    { property: 'og:image', content: '~/assets/images/og-properties.jpg' },
    { property: 'og:url', content: 'https://yourdomain.com/properties' },
  ],
});
</script>