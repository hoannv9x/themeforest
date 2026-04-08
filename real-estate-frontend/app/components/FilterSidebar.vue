<!-- components/FilterSidebar.vue -->
<template>
  <div class="p-4 bg-white rounded-xl shadow space-y-4">
    <h2 class="font-semibold text-lg">Filters</h2>

    <!-- Keyword -->
    <input
      v-model="filters.keyword"
      type="text"
      placeholder="Search..."
      class="w-full border rounded-lg px-3 py-2"
    />

    <!-- Price -->
    <div>
      <label class="text-sm text-gray-500">Max Price</label>
      <input
        v-model="filters.price"
        type="number"
        class="w-full border rounded-lg px-3 py-2"
      />
    </div>

    <!-- Submit -->
    <button @click="apply" class="w-full bg-blue-500 text-white py-2 rounded-lg">
      Apply
    </button>
  </div>
</template>

<script setup>
const emit = defineEmits(["change"]);

const filters = reactive({
  keyword: "",
  price: "",
});

const apply = () => {
  emit("change", { ...filters });
};
</script>
