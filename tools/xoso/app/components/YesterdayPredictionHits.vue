<template>
  <div class="bg-white border rounded-xl p-6 space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
      <div>
        <h3 class="text-lg font-bold">Gợi ý hôm qua ({{ data?.date || "--" }})</h3>
        <p class="text-sm text-gray-600">Đánh dấu số đã về theo kết quả thực tế.</p>
      </div>
      <div class="text-sm font-semibold text-green-700 bg-green-50 border border-green-200 rounded-lg px-3 py-2">
        Tổng số đã về: {{ totalHits }}
      </div>
    </div>

    <div v-if="!hasResult" class="text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg p-3">
      Chưa có kết quả để đối chiếu cho ngày này.
    </div>

    <div class="space-y-3">
      <h4 class="font-semibold">Gợi ý lô tô hôm qua</h4>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="(item, index) in lotoNumbers"
          :key="`loto-${index}-${item.number}`"
          class="px-3 py-2 rounded-lg border text-sm font-bold"
          :class="item.is_hit ? 'bg-green-500 border-green-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700'"
        >
          {{ item.number }}
        </div>
      </div>
      <p class="text-sm text-gray-600">Đã về: {{ lotoHitCount }}/{{ lotoTotal }}</p>
    </div>

    <div class="space-y-3">
      <h4 class="font-semibold">Gợi ý giải đặc biệt hôm qua</h4>
      <div class="flex flex-wrap gap-2">
        <div
          v-for="(item, index) in dbNumbers"
          :key="`db-${index}-${item.number}`"
          class="px-3 py-2 rounded-lg border text-sm font-bold"
          :class="item.is_hit ? 'bg-green-500 border-green-600 text-white' : 'bg-gray-50 border-gray-200 text-gray-700'"
        >
          {{ item.number }}
        </div>
      </div>
      <p class="text-sm text-gray-600">Đã về: {{ dbHitCount }}/{{ dbTotal }}</p>
    </div>
  </div>
</template>

<script setup>
const props = defineProps({
  data: {
    type: Object,
    default: () => ({}),
  },
});

const lotoNumbers = computed(() => props.data?.predictions?.ranking?.numbers || []);
const dbNumbers = computed(() => props.data?.predictions?.db_ranking?.numbers || []);
const hasResult = computed(() => !!props.data?.has_result);
const totalHits = computed(() => props.data?.stats?.total_hit_count || 0);
const lotoHitCount = computed(() => props.data?.stats?.loto_hit_count || 0);
const lotoTotal = computed(() => props.data?.stats?.loto_total || 0);
const dbHitCount = computed(() => props.data?.stats?.db_hit_count || 0);
const dbTotal = computed(() => props.data?.stats?.db_total || 0);
</script>
