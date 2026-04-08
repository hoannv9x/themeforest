<!-- components/Pagination.vue -->
<template>
  <div class="flex items-center justify-center gap-2 mt-6">
    <!-- Prev -->
    <button
      :disabled="page === 1"
      @click="change(page - 1)"
      class="px-3 py-1 border rounded disabled:opacity-50"
    >
      Prev
    </button>

    <!-- Pages -->
    <button
      v-for="p in totalPages"
      :key="p"
      @click="change(p)"
      class="px-3 py-1 border rounded"
      :class="p === page ? 'bg-blue-500 text-white' : ''"
    >
      {{ p }}
    </button>

    <!-- Next -->
    <button
      :disabled="page === totalPages"
      @click="change(page + 1)"
      class="px-3 py-1 border rounded disabled:opacity-50"
    >
      Next
    </button>
  </div>
</template>

<script setup>
const props = defineProps({
  page: Number,
  total: Number,
  perPage: { type: Number, default: 10 },
});

const emit = defineEmits(["update:page"]);

const totalPages = computed(() => Math.ceil(props.total / props.perPage));

const change = (p) => {
  emit("update:page", p);
};
</script>
