<template>
  <div class="bg-white p-4 rounded-xl shadow">
    <h3 class="font-bold mb-3">🔥 Lô gan</h3>

    <table class="w-full text-sm">
      <tr v-for="item in data?.slice(0, 10)" :key="item.number">
        <td>{{ item.number }}</td>
        <td>{{ item.current_gap }} ngày</td>
      </tr>
    </table>
  </div>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});
</script>
