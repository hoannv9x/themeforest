<template>
  <div class="bg-white p-4 rounded-xl shadow">
    <h3 class="font-bold mb-4">🔥 Heatmap 00–99</h3>

    <div class="grid grid-cols-10 gap-2 max-sm:grid-cols-5">
      <div
        v-for="item in numbers"
        :key="item.number"
        class="aspect-square flex items-center justify-center rounded-lg text-sm font-bold cursor-pointer transition hover:scale-105"
        :class="getColor(item)"
        :title="`Số ${item.number} - Gan: ${item.current_gap}`"
      >
        {{ item.number }}
      </div>
    </div>

    <!-- legend -->
    <div class="flex gap-4 mt-4 text-xs flex-wrap">
      <span class="flex items-center gap-1">
        <span class="w-3 h-3 bg-red-600 rounded"></span> Gan cao
      </span>
      <span class="flex items-center gap-1">
        <span class="w-3 h-3 bg-red-400 rounded"></span> Gan vừa
      </span>
      <span class="flex items-center gap-1">
        <span class="w-3 h-3 bg-green-500 rounded"></span> Tần suất cao
      </span>
      <span class="flex items-center gap-1">
        <span class="w-3 h-3 bg-yellow-400 rounded"></span> Chưa ra
      </span>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Array,
    default: () => [],
  },
});

const map = computed(() => {
  const m = {};
  props.data.forEach((i) => {
    m[i.number] = i;
  });
  return m;
});

const numbers = computed(() => {
  const arr = [];

  for (let i = 0; i < 100; i++) {
    const num = String(i).padStart(2, "0");
    arr.push(
      map.value[num] || {
        number: num,
        current_gap: 0,
        total_count: 0,
        never_hit: true,
      }
    );
  }

  return arr;
});

const getColor = (item) => {
  if (item.number == "10") {
    console.log(item);
  }
  if (item.never_hit || item.total_count == 0) return "bg-yellow-400 text-black";

  if (item.current_gap > 10) return "bg-red-600 text-white";
  if (item.current_gap > 5) return "bg-red-400 text-white";
  if (item.total_count > 50) return "bg-green-500 text-white";

  return "bg-gray-200";
};
</script>
