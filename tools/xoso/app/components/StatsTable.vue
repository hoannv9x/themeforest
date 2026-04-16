<template>
  <div class="bg-white p-4 rounded-xl shadow">
    <h3 class="font-bold mb-3 text-center">🔥 Lô gan</h3>

    <table class="w-full text-sm text-left rtl:text-right text-body">
      <thead
        class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-default"
      >
        <tr>
          <th scope="col" class="px-6 py-3 font-medium text-center">Number</th>
          <th scope="col" class="px-6 py-3 font-medium text-center">Ngày</th>
        </tr>
      </thead>
      <tr
        v-for="item in data?.slice(0, 10)"
        :key="item.number"
        class="bg-neutral-primary border-b border-default"
      >
        <th class="px-6 py-4 font-medium text-heading whitespace-nowrap text-center">{{ item.number }}</th>
        <td class="text-center">{{ item.current_gap }} ngày</td>
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
