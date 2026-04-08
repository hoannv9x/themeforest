<template>
  <div class="flex items-center space-x-4 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
    <img
      :src="agent.user.profile_picture || defaultAvatar"
      :alt="agent.user.name"
      class="w-16 h-16 rounded-full object-cover"
    />
    <div>
      <NuxtLink :to="`/agents/${agent.id}`" class="text-lg font-semibold text-blue-600 hover:underline">
        {{ agent.user.name }}
      </NuxtLink>
      <p class="text-gray-600 text-sm">{{ agent.agency_name }}</p>
      <p class="text-gray-500 text-xs">{{ agent.user.email }}</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';
import defaultAvatar from '~/assets/images/default-avatar.jpg'

const props = defineProps({
  agent: {
    type: Object,
    required: true,
  },
});
</script>