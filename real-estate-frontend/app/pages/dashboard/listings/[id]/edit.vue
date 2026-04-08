<template>
  <div>
    <h2 class="text-3xl font-semibold text-gray-800 mb-6">Edit Property Listing</h2>
    <PropertyForm :propertyId="route.params.id" /> <!-- Reusable form component -->
  </div>
</template>

<script setup>
import { useRoute } from '#app';
import PropertyForm from '~/components/PropertyForm.vue'; // Will be created in Step 7

const route = useRoute();
</script>