<template>
  <div class="container mx-auto px-4 py-8">
    <!-- Hero Section with Search Bar -->
    <section class="hero bg-blue-600 text-white py-20 rounded-lg shadow-lg mb-12">
      <div class="text-center">
        <h1 class="text-5xl font-bold mb-4">Find Your Dream Home</h1>
        <p class="text-xl mb-8">Search properties for sale and rent across the globe.</p>
        <SearchBar />
        <!-- Reusable SearchBar component -->
      </div>
    </section>

    <!-- Featured Properties Section -->
    <section class="mb-12">
      <h2 class="text-3xl font-semibold text-gray-800 mb-6">Featured Properties</h2>
      <SkeletonLoading
        v-if="loading"
        :count="6"
        :columns="3"
        :showImage="true"
        :rows="2"
      />
      <div
        v-else-if="featuredProperties.length"
        class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8"
      >
        <PropertyCard
          v-for="property in featuredProperties"
          :key="property.id"
          :property="property"
        />
      </div>
      <div v-else class="text-center text-gray-600">
        No featured properties available at the moment.
      </div>
    </section>

    <!-- Property Categories Section -->
    <section>
      <h2 class="text-3xl font-semibold text-gray-800 mb-6">Explore by Category</h2>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
        <NuxtLink to="/properties?type=apartment" class="category-card">
          <img
            :src="apartment"
            alt="Apartments"
            class="w-full h-32 object-cover rounded-md mb-2"
          />
          <h3 class="text-xl font-medium text-gray-700">Apartments</h3>
        </NuxtLink>
        <NuxtLink to="/properties?type=house" class="category-card">
          <img
            :src="house"
            alt="Houses"
            class="w-full h-32 object-cover rounded-md mb-2"
          />
          <h3 class="text-xl font-medium text-gray-700">Houses</h3>
        </NuxtLink>
        <NuxtLink to="/properties?type=condo" class="category-card">
          <img
            :src="condo"
            alt="Condos"
            class="w-full h-32 object-cover rounded-md mb-2"
          />
          <h3 class="text-xl font-medium text-gray-700">Condos</h3>
        </NuxtLink>
        <NuxtLink to="/properties?type=land" class="category-card">
          <img :src="land" alt="Land" class="w-full h-32 object-cover rounded-md mb-2" />
          <h3 class="text-xl font-medium text-gray-700">Land</h3>
        </NuxtLink>
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, onMounted } from "vue";
import { useNuxtApp } from "#app";
// Import components (will be created in Step 7)
import SearchBar from "~/components/SearchBar.vue";
import PropertyCard from "~/components/PropertyCard.vue";
import SkeletonLoading from "~/components/common/SkeletonLoading.vue";

// Image
import apartment from "~/assets/images/apartment.jpg";
import house from "~/assets/images/house.jpg";
import condo from "~/assets/images/condo.jpg";
import land from "~/assets/images/land.jpg";

const { $api } = useNuxtApp();
const featuredProperties = ref([]);
const loading = ref(true);

// Fetch featured properties on component mount
onMounted(async () => {
  try {
    const response = await $api.get("/properties", {
      params: { is_featured: true, per_page: 6 },
    });
    featuredProperties.value = response.data.data;
  } catch (error) {
    console.error("Error fetching featured properties:", error);
  } finally {
    loading.value = false;
  }
});

// SEO optimization for the home page
useHead({
  title: "Real Estate Listing - Find Your Dream Home",
  meta: [
    {
      name: "description",
      content:
        "Search properties for sale and rent. Find houses, apartments, condos, and land listings.",
    },
    { property: "og:title", content: "Real Estate Listing - Find Your Dream Home" },
    {
      property: "og:description",
      content:
        "Search properties for sale and rent. Find houses, apartments, condos, and land listings.",
    },
    { property: "og:image", content: "~/assets/images/og-home.jpg" },
    { property: "og:url", content: "https://yourdomain.com/" },
  ],
});
</script>

<style scoped>
.hero {
  background-image: url("~/assets/images/hero-bg.jpg");
  background-size: cover;
  background-position: center;
}
.category-card {
  @apply block p-4 bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300 text-center;
}
</style>
