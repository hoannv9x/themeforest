<template>
  <div class="bg-white p-4 rounded-xl shadow">
    <h3 class="font-bold mb-3 text-center">🔥 Lô gan</h3>

    <table class="w-full text-sm text-left rtl:text-right text-body">
      <thead
        class="text-sm text-body bg-neutral-secondary-soft border-b rounded-base border-default"
      >
        <tr>
          <th
            scope="col"
            class="px-6 py-3 max-sm:px-1 max-sm:py-1 font-medium text-center"
          >
            Number
          </th>
          <th scope="col" class="font-medium text-center">Gan</th>
          <th scope="col" class="font-medium text-center">Về gần nhất</th>
          <th scope="col" class="font-medium text-center">Gan cực đại</th>
        </tr>
      </thead>
      <tr
        v-for="item in data?.slice(0, 10)"
        :key="item.number"
        class="bg-neutral-primary border-b border-default max-sm:text-xs"
      >
        <th
          class="px-6 py-4 max-sm:px-3 max-sm:py-2 font-medium text-heading whitespace-nowrap text-center"
        >
          {{ item.number }}
        </th>
        <td class="text-center">{{ item.current_gap }} ngày</td>
        <td class="text-center">{{ formatDate(item.last_appeared_at) }}</td>
        <td class="text-center">{{ item.max_gap }} ngày</td>
      </tr>
    </table>
  </div>
</template>

<script setup>
import { useFormatters } from "~/composables/formatters";
const { formatDate } = useFormatters();
const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});
</script>
