<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex flex-col lg:flex-row gap-8">
      <!-- Filter Sidebar -->
      <aside class="lg:w-1/4">
        <div class="sticky top-24 max-h-[calc(100vh-120px)] overflow-y-auto">
          <h1 class="text-3xl font-bold text-gray-800 mb-3">Properties for Sale</h1>
          <FilterSidebar
            @apply-filters="handleApplyFilters"
            @city-change="handleCityChange"
            :cities="cities"
            :districts="districts"
            :propertyTypes="propertyTypes"
            :filtersDefault="currentFilters"
          />
          <!-- Reusable FilterSidebar component -->
        </div>
      </aside>

      <!-- Property Listings -->
      <main class="lg:w-3/4 mt-6 min-h-[calc(100vh-170px)]">
        <div class="flex justify-between items-center mb-6">
          <p class="text-gray-600">{{ totalProperties }} results found</p>
          <div class="flex items-center space-x-4">
            <label for="sort" class="text-gray-700">Sort by:</label>
            <select
              id="sort"
              v-model="sortBy"
              @change="fetchProperties"
              class="border rounded-md p-2"
            >
              <option value="published_at_desc">Newest</option>
              <option value="price_asc">Price: Low to High</option>
              <option value="price_desc">Price: High to Low</option>
              <option value="views_count_desc">Popularity</option>
            </select>
          </div>
        </div>
        <SkeletonLoading
          v-if="loading"
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
import { ref, watch } from "vue";
import { useRoute, useRouter, useNuxtApp } from "#app";
// Import components (will be created in Step 7)
import FilterSidebar from "~/components/FilterSidebar.vue";
import PropertyCard from "~/components/PropertyCard.vue";
import Pagination from "~/components/Pagination.vue";
import SkeletonLoading from "~/components/common/SkeletonLoading.vue";

const { $api } = useNuxtApp();
const route = useRoute();
const router = useRouter();

// ========== Reactive data ==========
const properties = ref([]);
const currentPage = ref(parseInt(route.query.page) || 1);
const totalPages = ref(1);
const totalProperties = ref(0);
const sortBy = ref(
  (route.query.sort_by || "published_at") + "_" + (route.query.sort_direction || "desc")
);
const currentFilters = ref({});
const loading = ref(true);
const cities = ref([]);
const districts = ref([]);
const propertyTypes = ref([]);

// ========== Methods ==========
/**
 * Fetch properties from the API
 */
const fetchProperties = async () => {
  loading.value = true;
  try {
    // Parse sortBy into column and direction
    const parts = sortBy.value.split("_");
    const sortDirection = parts.pop();
    const sortColumn = parts.join("_");

    // Build params: exclude min/max_price from query string
    const { min_price, max_price, ...queryParams } = currentFilters.value;

    const params = {
      page: currentPage.value,
      sort_by: sortColumn,
      sort_direction: sortDirection,
      min_price,
      max_price,
      ...queryParams,
    };

    // Update URL without triggering navigation
    router.replace({ query: { ...route.query, ...queryParams } });

    const { data } = await $api.get("/properties", { params });
    properties.value = data.data;
    currentPage.value = data.meta.current_page;
    totalPages.value = data.meta.last_page;
    totalProperties.value = data.meta.total;
  } catch (error) {
    console.error("Error fetching properties:", error);
  } finally {
    loading.value = false;
  }
};

/**
 * Handle filter changes from FilterSidebar
 */
const handleApplyFilters = (filters) => {
  currentFilters.value = filters;
  currentPage.value = 1; // Reset to first page on new filters
  fetchProperties();
};

/**
 * Handle page changes from Pagination component
 */
const handlePageChange = (page) => {
  currentPage.value = page;
  fetchProperties();
};

/**
 * Fetch initial filter data (cities, property types)
 */
const fetchFilterData = async () => {
  try {
    const [cityRes, typeRes] = await Promise.all([
      $api.get("/cities"),
      $api.get("/property-types"),
    ]);
    cities.value = cityRes.data.data;
    propertyTypes.value = typeRes.data.data;
    if (route.query.city_id) {
      handleCityChange(parseInt(route.query.city_id));
    }
  } catch (error) {
    console.error("Failed to fetch initial filter data:", error);
  }
};

/**
 * Handle city change to load districts
 */
const handleCityChange = (cityId) => {
  const city = cities.value.find((c) => c.id === cityId);
  districts.value = city?.districts || [];
};

// ========== Watchers ==========
/**
 * Watch for changes in route query parameters (e.g., direct URL changes)
 */
watch(route.query, () => {
  currentPage.value = parseInt(route.query.page) || 1;
  sortBy.value = route.query.sort_by || "published_at_desc";
  currentFilters.value = {
    city_id: route.query.city_id,
    property_type_id: route.query.property_type_id,
    min_price: route.query.min_price,
    max_price: route.query.max_price,
  };
  fetchProperties();
});

// ========== Lifecycle hooks ==========
onMounted(() => {
  currentFilters.value = {
    keyword: route.query?.keyword,
    bedrooms: route.query?.bedrooms || null,
    bathrooms: route.query?.bathrooms || null,
    property_type: route.query?.property_type || null,
    city_id: route.query?.city_id || null,
    district_id: route.query?.district_id || null,
  };
  fetchFilterData();
  fetchProperties();
});

// ========== SEO ==========
useHead({
  title: "Property Listings - Real Estate",
  meta: [
    {
      name: "description",
      content:
        "Browse all properties for sale and rent. Filter by price, city, and property type.",
    },
    { property: "og:title", content: "Property Listings - Real Estate" },
    {
      property: "og:description",
      content:
        "Browse all properties for sale and rent. Filter by price, city, and property type.",
    },
    { property: "og:image", content: "~/assets/images/og-properties.jpg" },
    { property: "og:url", content: "https://yourdomain.com/properties" },
  ],
});
</script>
