<template>
  <div class="container mx-auto px-4 py-8">
    <div v-if="property" class="bg-white rounded-lg shadow-lg overflow-hidden">
      <!-- Image Gallery -->
      <PropertyGallery :images="property.images" :rooms="property.rooms" />
      <!-- Reusable PropertyGallery component -->

      <div class="p-6">
        <h1 class="text-4xl font-bold text-gray-800 mb-4">{{ property.title }}</h1>
        <p
          class="text-2xl text-blue-600 font-semibold mb-4 bg-blue-100 w-fit px-4 py-2 rounded"
        >
          {{ formatPrice(property.price, property.currency) }}
        </p>

        <!-- Property Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
          <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-3">Overview</h2>
            <ul class="space-y-4 text-gray-700">
              <li class="flex gap-2">
                <img :src="iconHome" alt="iconHome" class="h-fit" />
                <strong>Address:</strong>
                {{ property.address }},
                {{ property.district?.name ? property.district.name + ", " : "" }}
                {{ property.city.name }}
              </li>
              <li class="grid grid-cols-2 gap-2 items-center">
                <div class="flex gap-2">
                  <img :src="iconProperty" alt="iconProperty" class="h-fit" />
                  <strong>Property Type:</strong> {{ property.property_type.name }}
                </div>
                <div class="flex gap-2">
                  <img :src="iconStatusHome" alt="iconStatus" class="h-fit" />
                  <strong>Status:</strong>
                  <span
                    :class="{
                      'text-green-600': property.status === 'active',
                      'text-red-600': property.status === 'sold',
                    }"
                    >{{ property.status }}</span
                  >
                </div>
              </li>
              <li
                v-if="property.bedrooms || property.bathrooms"
                class="grid grid-cols-2 gap-2 items-center"
              >
                <div v-if="property.bedrooms" class="flex gap-2">
                  <img :src="iconBedroom" alt="iconBedroom" class="h-fit" />
                  <strong>Bedrooms:</strong> {{ property.bedrooms }}
                </div>
                <div v-if="property.bathrooms" class="flex gap-2">
                  <img :src="iconBathroom" alt="iconBathroom" class="h-fit" />
                  <strong>Bathrooms:</strong> {{ property.bathrooms }}
                </div>
              </li>
              <li
                class="grid grid-cols-2 gap-2 items-center"
                v-if="property.area_sqft || property.year_built"
              >
                <div v-if="property.area_sqft" class="flex gap-2">
                  <img :src="iconArea" alt="iconArea" class="h-fit" />
                  <strong>Area:</strong> {{ property.area_sqft }} sqft
                </div>
                <div v-if="property.year_built" class="flex gap-2">
                  <img :src="iconAge" alt="iconAge" class="h-fit" />
                  <strong>Year Built:</strong> {{ property.year_built }}
                </div>
              </li>
            </ul>
          </div>
          <div>
            <h2 class="text-2xl font-semibold text-gray-800 mb-3">Description</h2>
            <p class="text-gray-700 leading-relaxed">{{ property.description }}</p>
          </div>
        </div>

        <!-- Agent Contact Form -->
        <section
          v-if="property.agent"
          class="bg-gray-50 p-6 rounded-lg shadow-inner mb-8"
        >
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Contact Agent</h2>
          <AgentCard :agent="property.agent" />
          <!-- Reusable AgentCard component -->
          <br />
          <ContactAgentForm :agentId="property.agent.id" :propertyId="property.id" />
          <!-- Reusable ContactAgentForm component -->
        </section>

        <!-- Map View (Optional - will be covered in Advanced Features) -->
        <section v-if="property.latitude && property.longitude" class="mb-8">
          <h2 class="text-2xl font-semibold text-gray-800 mb-4">Location</h2>
          <PropertyMap
            :latitude="property.latitude"
            :longitude="property.longitude"
            :markerTitle="property.title"
          />
        </section>

        <!-- Favorite Button -->
        <div class="text-right">
          <FavoriteButton :propertyId="property.id" />
          <!-- Reusable FavoriteButton component -->
        </div>
      </div>
    </div>

    <!-- Related Properties -->
    <section v-if="relatedProperties.length" class="mt-12">
      <h2 class="text-3xl font-bold text-gray-800 mb-6">Related Properties</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <PropertyCard
          v-for="related in relatedProperties"
          :key="related.id"
          :property="related"
        />
      </div>
    </section>
    <div v-else class="text-center text-gray-600 py-20">Loading property details...</div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from "vue";
import { useRoute, useNuxtApp } from "#app";
import { formatPrice } from "~/utils/format";
// Import components (will be created in Step 7)
import PropertyGallery from "~/components/PropertyGallery.vue";
import AgentCard from "~/components/AgentCard.vue";
import ContactAgentForm from "~/components/ContactAgentForm.vue";
import FavoriteButton from "~/components/FavoriteButton.vue";
import PropertyMap from "~/components/PropertyMap.vue";
import PropertyCard from "~/components/PropertyCard.vue";

// import image, icon
import defaultProperty from "~/assets/images/og-default-property.jpg";
import iconHome from "~/assets/icons/home.svg";
import iconProperty from "~/assets/icons/property.svg";
import iconStatusHome from "~/assets/icons/status_home.svg";
import iconAge from "~/assets/icons/age.svg";
import iconBathroom from "~/assets/icons/bathroom.svg";
import iconBedroom from "~/assets/icons/bedroom.svg";
import iconArea from "~/assets/icons/area.svg";

const { $api } = useNuxtApp();
const route = useRoute();
const property = ref(null);
const relatedProperties = ref([]);

// Fetch property details based on the dynamic ID
const fetchProperty = async () => {
  try {
    const response = await $api.get(`/properties/${route.params.id}`);
    property.value = response.data.data;
    fetchRelatedProperties();
  } catch (error) {
    console.error("Error fetching property details:", error);
  }
};

const fetchRelatedProperties = async () => {
  try {
    const response = await $api.get(`/properties/${route.params.id}/related`);
    relatedProperties.value = response.data.data;
  } catch (error) {
    console.error("Error fetching related properties:", error);
  }
};

// Watch for route changes to re-fetch property (e.g., clicking a related property)
watch(() => route.params.id, fetchProperty);

// Fetch property on component mount
onMounted(fetchProperty);

// SEO optimization for the property detail page
useHead(() => ({
  title: property.value ? `${property.value.title} - Real Estate` : "Property Details",
  meta: [
    {
      name: "description",
      content: property.value
        ? property.value.description.substring(0, 160)
        : "Detailed information about a real estate property.",
    },
    {
      property: "og:title",
      content: property.value
        ? `${property.value.title} - Real Estate`
        : "Property Details",
    },
    {
      property: "og:description",
      content: property.value
        ? property.value.description.substring(0, 160)
        : "Detailed information about a real estate property.",
    },
    {
      property: "og:image",
      content:
        property.value && property.value.images.length > 0
          ? property.value.images[0].image_path
          : defaultProperty,
    }, // First image or default
    {
      property: "og:url",
      content: `https://yourdomain.com/properties/${route.params.id}`,
    },
  ],
}));
</script>
