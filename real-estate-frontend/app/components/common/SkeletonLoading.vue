<template>
  <div :class="gridClass">
    <div v-for="i in count" :key="i" class="space-y-4 animate-pulse">
      <!-- Image -->
      <div
        v-if="showImage"
        :class="[
          'rounded-xl bg-gray-300 dark:bg-gray-700 relative overflow-hidden',
          imageClass,
        ]"
      >
        <!-- shimmer -->
        <div
          v-if="shimmer"
          class="absolute inset-0 -translate-x-full bg-gradient-to-r from-transparent via-white/40 to-transparent animate-shimmer"
        ></div>
      </div>

      <!-- Title -->
      <div class="h-4 bg-gray-300 dark:bg-gray-700 rounded" :class="titleWidth"></div>

      <!-- Description -->
      <div
        v-if="rows > 1"
        class="h-4 bg-gray-300 dark:bg-gray-700 rounded"
        :class="descWidth"
      ></div>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  count: { type: Number, default: 6 }, // số card
  columns: { type: Number, default: 3 }, // số cột
  showImage: { type: Boolean, default: true },
  shimmer: { type: Boolean, default: true },
  rows: { type: Number, default: 2 }, // số dòng text
});

/**
 * Grid responsive
 */
const gridClass = computed(() => {
  const map = {
    1: "grid-cols-1",
    2: "grid-cols-1 sm:grid-cols-2",
    3: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-3",
    4: "grid-cols-1 sm:grid-cols-2 lg:grid-cols-4",
  };
  return `grid gap-6 ${map[props.columns] || map[3]}`;
});

/**
 * Style
 */
const imageClass = "h-40 w-full";
const titleWidth = "w-3/4";
const descWidth = "w-1/2";
</script>

<style>
@keyframes shimmer {
  100% {
    transform: translateX(100%);
  }
}
.animate-shimmer {
  animation: shimmer 1.5s infinite;
}
</style>
