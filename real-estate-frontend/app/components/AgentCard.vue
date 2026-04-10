<template>
  <div class="bg-white rounded-lg shadow-md overflow-hidden group">
    <div class="relative">
      <NuxtLink :to="`/agents/${agent.id}`">
        <img
          :src="
            agent.avatar_url ||
            'https://via.placeholder.com/400x300.png/E2E8F0/94A3B8?text=No+Image'
          "
          :alt="`Photo of ${agent.name}`"
          class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-300"
        />
      </NuxtLink>
      <div
        v-if="agent.properties_count > 0"
        class="absolute top-4 right-4 bg-blue-600 text-white text-sm font-semibold px-3 py-1 rounded-full"
      >
        {{ agent.properties_count }} Properties
      </div>
    </div>
    <div class="p-6 text-center">
      <h3 class="text-2xl font-bold mb-1">
        <NuxtLink
          :to="`/agents/${agent.id}`"
          class="hover:text-blue-700 transition-colors"
          >{{ agent.name }}</NuxtLink
        >
      </h3>
      <p class="text-gray-500 mb-4">{{ agent.title || "Real Estate Agent" }}</p>

      <div class="flex justify-center space-x-4 mb-4">
        <a
          :href="`mailto:${agent.email}`"
          class="text-gray-400 hover:text-blue-600"
          title="Email"
        >
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M2.003 5.884L10 9.882l7.997-3.998A2 2 0 0016 4H4a2 2 0 00-1.997 1.884z"
            ></path>
            <path d="M18 8.118l-8 4-8-4V14a2 2 0 002 2h12a2 2 0 002-2V8.118z"></path>
          </svg>
        </a>
        <a
          v-if="agent.phone"
          :href="`tel:${agent.phone}`"
          class="text-gray-400 hover:text-blue-600"
          title="Phone"
        >
          <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
            <path
              d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z"
            ></path>
          </svg>
        </a>
      </div>

      <NuxtLink
        :to="`/agents/${agent.id}`"
        class="inline-block w-full bg-gray-100 text-gray-800 font-semibold py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors"
      >
        View Profile
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
defineProps({
  agent: {
    type: Object,
    required: true,
  },
});
</script>
