<template>
  <div class="property-gallery">
    <!-- Main Image -->
    <div class="main-image mb-4">
      <VirtualTour :rooms="rooms" v-if="rooms.length > 0 && !selectedImage" />
      <img
        v-else-if="selectedImage"
        :src="selectedImage.image_path"
        :alt="selectedImage.alt || 'Property Image'"
        class="w-full h-96 object-cover rounded-t-lg shadow-md"
      />
      <div
        v-else
        class="w-full h-96 bg-gray-200 rounded-lg flex items-center justify-center"
      >
        <span class="text-gray-500">No Image Available</span>
      </div>
    </div>

    <!-- Thumbnails -->
    <div
      v-if="(images && images.length > 1) || rooms.length > 0"
      class="thumbnails grid grid-cols-3 sm:grid-cols-4 md:grid-cols-5 lg:grid-cols-6 gap-2 px-2"
    >
      <!-- 360 Tour Thumbnail -->
      <div
        v-if="rooms.length > 0"
        @click="show360Tour"
        class="cursor-pointer border rounded flex items-center justify-center flex-col text-center bg-gray-100 hover:bg-gray-200"
        :class="{ 'border-blue-500': !selectedImage }"
      >
        <svg
          xmlns="http://www.w3.org/2000/svg"
          class="h-8 w-8 text-gray-600"
          fill="none"
          viewBox="0 0 24 24"
          stroke="currentColor"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"
          />
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"
          />
        </svg>
        <span class="text-xs font-semibold text-gray-700 mt-1">360° Tour</span>
      </div>
      <!-- Image Thumbnails -->
      <div
        v-for="(image, index) in images"
        :key="index"
        @click="selectImage(image)"
        class="cursor-pointer border rounded"
        :class="{
          'border-blue-500':
            selectedImage && selectedImage.image_path === image.image_path,
        }"
      >
        <img
          :src="image.image_path"
          :alt="image.alt || `${title || 'Property Image'} ${index + 1}`"
          class="w-full h-24 object-cover rounded"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from "vue";
import VirtualTour from "~/components/VirtualTour.vue";

const props = defineProps({
  images: {
    type: Array,
    required: true,
    default: () => [],
  },
  rooms: {
    type: Array,
    required: true,
    default: () => [],
  },
});

const selectedImage = ref(null);

// Function to change the main image
const selectImage = (image) => {
  selectedImage.value = image;
};

// Function to show the 360 tour
const show360Tour = () => {
  selectedImage.value = null;
};

// Initialize selectedImage with the first image or set to null if no images
watch(
  () => props.images,
  (newImages) => {
    if (newImages && newImages.length > 0) {
      selectedImage.value = newImages[0];
    } else {
      selectedImage.value = null;
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.thumbnails .cursor-pointer {
  transition: border-color 0.3s;
}
</style>
