<template>
  <NuxtLink
    :to="`/properties/${property.slug || property.id}`"
    class="block bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden"
  >
    <div class="relative h-48 overflow-hidden">
      <img
        :src="
          property.images && property.images.length > 0
            ? property.images[0].image_path
            : defaultProperty
        "
        :alt="property.title"
        class="w-full h-full object-cover transition-transform duration-300 hover:scale-105"
      />
      <div
        v-if="property.is_featured"
        class="absolute top-2 left-2 bg-blue-600 text-white text-xs font-semibold px-2 py-1 rounded-full"
      >
        Featured
      </div>
      <div
        class="absolute bottom-2 right-2 bg-black bg-opacity-50 text-white text-sm px-2 py-1 rounded-md"
      >
        {{ formatPrice(property.price, property.currency) }}
      </div>
    </div>
    <div class="p-4">
      <h3 class="text-xl font-semibold text-gray-800 mb-2 truncate">
        {{ property.title }}
      </h3>
      <p class="text-gray-600 text-sm mb-2 flex items-center">
        <svg
          class="w-4 h-4 mr-1 text-gray-500"
          fill="none"
          stroke="currentColor"
          viewBox="0 0 24 24"
          xmlns="http://www.w3.org/2000/svg"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"
          ></path>
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"
          ></path>
        </svg>
        {{ property.address }}, {{ property.city.name }}
      </p>
      <div
        class="flex items-center justify-between text-gray-700 text-sm flex-wrap gap-3"
      >
        <span v-if="property.bedrooms" class="flex items-center">
          <svg
            class="w-4 h-4 mr-1"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            ></path>
          </svg>
          {{ property.bedrooms }} Beds
        </span>
        <span v-if="property.bathrooms" class="flex items-center">
          <svg
            class="w-4 h-4 mr-1"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M9 5H7a2 2 0 002 2h2a2 2 0 002-2V3m2 4h2a2 2 0 002-2V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"
            ></path>
          </svg>
          {{ property.bathrooms }} Baths
        </span>
        <span v-if="property.area_sqft" class="flex items-center">
          <svg
            class="w-4 h-4 mr-1"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"
            ></path>
          </svg>
          {{ property.area_sqft }} sqft
        </span>
      </div>
      <div v-if="showActions" class="mt-4 flex justify-end space-x-2">
        <NuxtLink
          :to="`/dashboard/listings/${property.id}/edit`"
          class="text-blue-600 hover:text-blue-800"
          >Edit</NuxtLink
        >
        <button @click.prevent="deleteProperty" class="text-red-600 hover:text-red-800">
          Delete
        </button>
      </div>
    </div>
  </NuxtLink>
</template>

<script setup>
import { defineProps, defineEmits } from "vue";
import defaultProperty from "~/assets/images/default-property.jpg";
import { useNuxtApp } from "#app";
import { formatPrice } from "~/utils/format";

const props = defineProps({
  property: {
    type: Object,
    required: true,
  },
  showActions: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(["property-deleted"]);
const { $api } = useNuxtApp();

const deleteProperty = async () => {
  if (confirm("Are you sure you want to delete this property?")) {
    try {
      await $api.delete(`/properties/${props.property.id}`);
      emit("property-deleted", props.property.id);
      alert("Property deleted successfully!");
    } catch (error) {
      console.error("Error deleting property:", error);
      alert("Failed to delete property.");
    }
  }
};
</script>
